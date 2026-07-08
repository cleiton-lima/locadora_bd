<?php

namespace Tests\Feature;

use App\Support\Roles;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class BcnfApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSchema();
        $this->seedBaseData();
    }

    public function test_empresa_uses_usuario_id(): void
    {
        $response = $this->postJson('/api/empresas', [
            'usuario_id' => 1,
            'cnpj' => '12345678000199',
        ], $this->authHeaders([]));

        $response->assertCreated()
            ->assertJsonPath('id', 1);

        $this->assertDatabaseHas('Empresa', [
            'Usuario_id' => 1,
            'CNPJ' => '12345678000199',
        ]);

        $this->getJson('/api/empresas/1', $this->authHeaders([]))
            ->assertOk()
            ->assertJsonPath('Usuario_id', 1);
    }

    public function test_cnh_rejects_duplicate_number(): void
    {
        $payload = [
            'numero' => '00012345678',
            'estado' => 'RN',
            'categoria' => 'B',
            'data_emissao' => '2020-01-01',
            'data_validade' => '2030-01-01',
        ];

        $this->postJson('/api/cnhs', $payload, $this->authHeaders([]))->assertCreated();
        $this->postJson('/api/cnhs', $payload, $this->authHeaders([]))->assertStatus(409);
    }

    public function test_pessoa_fisica_uses_existing_cnh_numero(): void
    {
        $this->insertCnh('00012345678');

        $response = $this->postJson('/api/pessoas-fisicas', [
            'cliente_id' => 1,
            'cpf' => '12345678901',
            'cnh_numero' => '00012345678',
        ], $this->authHeaders([]));

        $response->assertCreated()
            ->assertJsonPath('id', 1);

        $this->assertDatabaseHas('Pessoa_Fisica', [
            'Cliente_id' => 1,
            'CPF' => '12345678901',
            'CNH_numero' => '00012345678',
        ]);
    }

    public function test_contrato_frota_validates_empresa_by_usuario_id(): void
    {
        DB::table('Empresa')->insert([
            'Usuario_id' => 1,
            'CNPJ' => '12345678000199',
        ]);

        $response = $this->postJson('/api/contratos-frota', [
            'gerente_comercial_id' => 10,
            'empresa_id' => 1,
            'data_inicio' => '2026-01-01 08:00:00',
            'data_final' => '2026-12-31 18:00:00',
            'quantidade_veiculos' => 3,
        ], $this->authHeaders([Roles::GERENTE_COMERCIAL]));

        $response->assertCreated();
    }

    public function test_aluguel_blocks_vehicle_with_active_rental(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'DISPONIVEL');
        DB::table('Aluguel')->insert([
            'id' => 99,
            'Atendente_entrega_id' => 20,
            'Atendente_devolucao_id' => null,
            'Pessoa_Fisica_id' => 1,
            'status' => 'ATIVO',
            'valor' => 100,
            'tipo' => 'CURTA_DURACAO',
            'data_inicial' => '2026-01-01 08:00:00',
            'data_final' => null,
            'data_final_prevista' => '2026-01-01 12:00:00',
            'Contrato_frota_id' => null,
            'Veiculo_id' => 100,
        ]);

        $this->postJson('/api/alugueis', $this->rentalPayload(), $this->authHeaders([Roles::ATENDENTE]))
            ->assertStatus(409);
    }

    public function test_aluguel_blocks_vehicle_with_alugado_status(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'ALUGADO');

        $this->postJson('/api/alugueis', $this->rentalPayload(), $this->authHeaders([Roles::ATENDENTE]))
            ->assertStatus(409);
    }

    public function test_aluguel_blocks_person_rental_for_vehicle_in_active_fleet_contract(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'DISPONIVEL');
        DB::table('Contrato_Frota')->insert([
            'id' => 70,
            'Gerente_Comercial_id' => 10,
            'Empresa_id' => 1,
            'data_inicio' => '2026-01-01 08:00:00',
            'data_final' => '2026-12-31 18:00:00',
            'quantidade_veiculos' => 1,
        ]);
        DB::table('Aluguel')->insert([
            'id' => 99,
            'Atendente_entrega_id' => 20,
            'Atendente_devolucao_id' => null,
            'Pessoa_Fisica_id' => null,
            'status' => 'ATIVO',
            'valor' => 100,
            'tipo' => 'LONGA_DURACAO',
            'data_inicial' => '2026-01-01 08:00:00',
            'data_final' => null,
            'data_final_prevista' => '2026-06-01 08:00:00',
            'Contrato_frota_id' => 70,
            'Veiculo_id' => 100,
        ]);

        $this->postJson('/api/alugueis', $this->rentalPayload(), $this->authHeaders([Roles::ATENDENTE]))
            ->assertStatus(409);
    }

    public function test_creating_aluguel_calculates_value_and_marks_vehicle_as_alugado(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'DISPONIVEL');

        $response = $this->postJson('/api/alugueis', $this->rentalPayload([
            'valor' => 1,
        ]), $this->authHeaders([Roles::ATENDENTE]));

        $response->assertCreated();

        $aluguel = DB::table('Aluguel')->where('id', $response->json('id'))->first();
        $this->assertEquals(140.00, (float) $aluguel->valor);
        $this->assertDatabaseHas('Veiculo', [
            'id' => 100,
            'status' => 'ALUGADO',
        ]);
    }

    public function test_devolver_aluguel_finalizes_rental_and_marks_vehicle_available(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'ALUGADO');
        DB::table('Aluguel')->insert([
            'id' => 99,
            'Atendente_entrega_id' => 20,
            'Atendente_devolucao_id' => null,
            'Pessoa_Fisica_id' => 1,
            'status' => 'ATIVO',
            'valor' => 120,
            'tipo' => 'CURTA_DURACAO',
            'data_inicial' => '2026-01-01 08:00:00',
            'data_final' => null,
            'data_final_prevista' => '2026-01-01 12:00:00',
            'Contrato_frota_id' => null,
            'Veiculo_id' => 100,
        ]);

        $this->patchJson('/api/alugueis/99/devolver', [
            'atendente_devolucao_id' => 21,
            'data_final' => '2026-01-01 11:30:00',
        ], $this->authHeaders([Roles::ATENDENTE]))->assertOk();

        $this->assertDatabaseHas('Aluguel', [
            'id' => 99,
            'Atendente_devolucao_id' => 21,
            'status' => 'FINALIZADO',
        ]);
        $this->assertDatabaseHas('Veiculo', [
            'id' => 100,
            'status' => 'DISPONIVEL',
        ]);
    }

    public function test_aluguel_blocks_when_cnh_is_expired(): void
    {
        DB::table('CNH')->insert([
            'numero' => '00099999999',
            'estado' => 'RN',
            'categoria' => 'B',
            'data_emissao' => '2010-01-01',
            'data_validade' => '2020-01-01',
        ]);
        DB::table('Pessoa_Fisica')->insert([
            'Cliente_id' => 2,
            'CPF' => '98765432100',
            'CNH_numero' => '00099999999',
        ]);
        $this->insertVeiculo(status: 'DISPONIVEL');

        $response = $this->postJson('/api/alugueis', $this->rentalPayload([
            'pessoa_fisica_id' => 2,
        ]), $this->authHeaders([Roles::ATENDENTE]));

        $response->assertStatus(409)
            ->assertJsonPath('message', 'CNH vencida para a data de retirada.');
    }

    public function test_venda_blocks_when_vehicle_is_alugado(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'ALUGADO');

        $response = $this->postJson('/api/vendas', [
            'valor' => 45000,
            'status' => 'CONCLUIDA',
            'veiculo_id' => 100,
            'pessoa_fisica_id' => 1,
            'gerente_comercial_funcionario_id' => 10,
        ], $this->authHeaders([Roles::GERENTE_COMERCIAL]));

        $response->assertStatus(409)
            ->assertJsonPath('message', 'Veículo indisponível para venda: está alugado.');
    }

    public function test_venda_creates_and_marks_vehicle_as_vendido(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'DISPONIVEL');

        $response = $this->postJson('/api/vendas', [
            'valor' => 45000,
            'status' => 'CONCLUIDA',
            'veiculo_id' => 100,
            'pessoa_fisica_id' => 1,
            'gerente_comercial_funcionario_id' => 10,
        ], $this->authHeaders([Roles::GERENTE_COMERCIAL]));

        $response->assertCreated();

        $this->assertDatabaseHas('Veiculo', [
            'id' => 100,
            'status' => 'VENDIDO',
        ]);
    }

    public function test_encerrar_contrato_frota_finalizes_rentals_and_frees_vehicles(): void
    {
        $this->insertVeiculo(status: 'ALUGADO');
        DB::table('Contrato_Frota')->insert([
            'id' => 70,
            'Gerente_Comercial_id' => 10,
            'Empresa_id' => 1,
            'data_inicio' => '2026-01-01 08:00:00',
            'data_final' => '2026-12-31 18:00:00',
            'quantidade_veiculos' => 1,
        ]);
        DB::table('Aluguel')->insert([
            'id' => 99,
            'Atendente_entrega_id' => 20,
            'Atendente_devolucao_id' => null,
            'Pessoa_Fisica_id' => null,
            'status' => 'ATIVO',
            'valor' => 500,
            'tipo' => 'LONGA_DURACAO',
            'data_inicial' => '2026-01-01 08:00:00',
            'data_final' => null,
            'data_final_prevista' => '2026-12-31 18:00:00',
            'Contrato_frota_id' => 70,
            'Veiculo_id' => 100,
        ]);

        $response = $this->patchJson('/api/contratos-frota/70/encerrar', [
            'data_final' => '2026-06-15 10:00:00',
        ], $this->authHeaders([Roles::GERENTE_COMERCIAL]));

        $response->assertOk()->assertJsonPath('veiculos_liberados', 1);

        $this->assertDatabaseHas('Aluguel', [
            'id' => 99,
            'status' => 'FINALIZADO',
            'data_final' => '2026-06-15 10:00:00',
        ]);
        $this->assertDatabaseHas('Veiculo', [
            'id' => 100,
            'status' => 'DISPONIVEL',
        ]);
        $this->assertDatabaseHas('Contrato_Frota', [
            'id' => 70,
            'data_final' => '2026-06-15 10:00:00',
        ]);
    }

    public function test_normalized_address_uses_cep_and_rejects_denormalized_fields(): void
    {
        $this->insertCep();

        $this->postJson('/api/usuarios-enderecos', [
            'usuario_id' => 1,
            'numero' => '100',
            'complemento' => null,
            'referencia' => null,
            'cep' => '59000000',
        ], $this->authHeaders([]))->assertCreated();

        $this->postJson('/api/usuarios-enderecos', [
            'usuario_id' => 2,
            'numero' => '200',
            'cep' => '59000000',
            'logradouro' => 'Rua antiga',
        ], $this->authHeaders([]))->assertUnprocessable();
    }

    public function test_login_with_valid_credentials_returns_tokens(): void
    {
        $this->insertUsuarioComSenha(500, 'clientept', 'cliente@example.com', 'segredo123');

        $this->postJson('/api/auth/login', [
            'login' => 'clientept',
            'senha' => 'segredo123',
        ])->assertOk()->assertJsonStructure(['access_token', 'refresh_token', 'expires_in', 'usuario', 'roles']);
    }

    public function test_login_with_invalid_credentials_returns_401(): void
    {
        $this->insertUsuarioComSenha(500, 'clientept', 'cliente@example.com', 'segredo123');

        $this->postJson('/api/auth/login', [
            'login' => 'clientept',
            'senha' => 'errada',
        ])->assertStatus(401);
    }

    public function test_me_requires_valid_token_and_returns_roles(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);

        $this->insertPessoaFisica();

        $this->getJson('/api/auth/me', $this->authHeaders([Roles::CLIENTE_PF], 1))
            ->assertOk()
            ->assertJsonPath('roles.0', Roles::CLIENTE_PF)
            ->assertJsonPath('usuario.id', 1);
    }

    public function test_refresh_rotates_token_and_revokes_previous(): void
    {
        $this->insertUsuarioComSenha(500, 'clientept', 'cliente@example.com', 'segredo123');
        $login = $this->postJson('/api/auth/login', ['login' => 'clientept', 'senha' => 'segredo123']);
        $refreshToken = $login->json('refresh_token');

        $renovado = $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken]);
        $renovado->assertOk()->assertJsonStructure(['access_token', 'refresh_token', 'expires_in']);
        $this->assertNotSame($refreshToken, $renovado->json('refresh_token'));

        $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken])->assertStatus(401);
    }

    public function test_logout_revokes_refresh_token(): void
    {
        $this->insertUsuarioComSenha(500, 'clientept', 'cliente@example.com', 'segredo123');
        $login = $this->postJson('/api/auth/login', ['login' => 'clientept', 'senha' => 'segredo123']);
        $refreshToken = $login->json('refresh_token');

        $this->postJson('/api/auth/logout', ['refresh_token' => $refreshToken])->assertOk();
        $this->postJson('/api/auth/refresh', ['refresh_token' => $refreshToken])->assertStatus(401);
    }

    public function test_role_middleware_blocks_wrong_profile(): void
    {
        $this->insertPessoaFisica();
        $this->insertVeiculo(status: 'DISPONIVEL');

        $this->postJson('/api/alugueis', $this->rentalPayload(), $this->authHeaders([Roles::CLIENTE_PF]))
            ->assertStatus(403);
    }

    public function test_google_callback_creates_new_usuario_and_returns_tokens(): void
    {
        $googleUser = new class
        {
            public function getId(): string
            {
                return 'google-123';
            }

            public function getEmail(): string
            {
                return 'novo@gmail.com';
            }

            public function getName(): string
            {
                return 'Novo Usuario';
            }
        };

        Socialite::shouldReceive('driver->stateless->user')->andReturn($googleUser);

        $response = $this->get('/api/auth/google/callback');

        $response->assertRedirect();
        $this->assertStringContainsString('access_token=', (string) $response->headers->get('Location'));

        $this->assertDatabaseHas('Usuario', ['email' => 'novo@gmail.com']);
        $usuario = DB::table('Usuario')->where('email', 'novo@gmail.com')->first();
        $this->assertDatabaseHas('Usuario_google', [
            'Usuario_id' => $usuario->id,
            'google_id' => 'google-123',
        ]);
    }

    private function insertUsuarioComSenha(int $id, string $username, string $email, string $senha): void
    {
        DB::table('Usuario')->insert([
            'id' => $id,
            'username' => $username,
            'nome' => 'Usuario Teste',
            'email' => $email,
            'ultimo_acesso' => null,
            'data_cadastro' => '2026-01-01 00:00:00',
            'ativo' => 1,
            'senha_hash' => Hash::make($senha),
        ]);
    }

    private function createSchema(): void
    {
        foreach ([
            'Refresh_Token', 'Usuario_google',
            'Filial_telefone', 'Filial_endereco', 'Montadora_endereco', 'Oficina_endereco',
            'Usuario_endereco', 'CEP', 'Logradouro', 'Bairro', 'Cidade', 'Usuario_telefone',
            'Telefone_montadora', 'Telefone_oficina', 'Venda', 'Aluguel', 'Atendente',
            'Pessoa_Fisica', 'CNH', 'Estado', 'Contrato_Frota', 'Empresa', 'Servico',
            'Veiculo', 'Administrador', 'Lote', 'Gerente_Comercial', 'Funcionario',
            'Usuario', 'Filial', 'Montadora', 'Oficina',
        ] as $table) {
            DB::statement("DROP TABLE IF EXISTS {$table}");
        }

        DB::statement('CREATE TABLE Usuario (id INTEGER PRIMARY KEY, username TEXT UNIQUE, nome TEXT, email TEXT UNIQUE, ultimo_acesso TEXT NULL, data_cadastro TEXT, ativo INTEGER, senha_hash TEXT)');
        DB::statement('CREATE TABLE Usuario_google (Usuario_id INTEGER PRIMARY KEY, google_id TEXT UNIQUE)');
        DB::statement('CREATE TABLE Refresh_Token (id INTEGER PRIMARY KEY AUTOINCREMENT, Usuario_id INTEGER, token_hash TEXT UNIQUE, expires_at TEXT, revoked_at TEXT NULL, created_at TEXT)');
        DB::statement('CREATE TABLE Filial (id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE Oficina (id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE Montadora (id INTEGER PRIMARY KEY, nome TEXT)');
        DB::statement('CREATE TABLE Funcionario (Usuario_id INTEGER PRIMARY KEY, nome TEXT, sobrenome TEXT, telefone TEXT, Filial_id INTEGER)');
        DB::statement('CREATE TABLE Gerente_Comercial (Funcionario_id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE Administrador (Funcionario_id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE Atendente (Funcionario_id INTEGER PRIMARY KEY)');
        DB::statement('CREATE TABLE Empresa (Usuario_id INTEGER PRIMARY KEY, CNPJ TEXT UNIQUE)');
        DB::statement('CREATE TABLE Contrato_Frota (id INTEGER PRIMARY KEY AUTOINCREMENT, Gerente_Comercial_id INTEGER, Empresa_id INTEGER, data_inicio TEXT, data_final TEXT, quantidade_veiculos INTEGER)');
        DB::statement('CREATE TABLE Estado (sigla TEXT PRIMARY KEY, nome TEXT UNIQUE)');
        DB::statement('CREATE TABLE CNH (numero TEXT PRIMARY KEY, estado TEXT, categoria TEXT, data_emissao TEXT, data_validade TEXT)');
        DB::statement('CREATE TABLE Pessoa_Fisica (Cliente_id INTEGER PRIMARY KEY, CPF TEXT UNIQUE, CNH_numero TEXT UNIQUE)');
        DB::statement('CREATE TABLE Lote (id INTEGER PRIMARY KEY, Gerente_Comercial_Funcionario_id INTEGER, Montadora_id INTEGER, preco_total REAL, quantidade_veiculos INTEGER)');
        DB::statement('CREATE TABLE Veiculo (id INTEGER PRIMARY KEY, status TEXT, finalidade TEXT, placa TEXT UNIQUE, grupo TEXT, quilometragem INTEGER, Administrador_Funcionario_id INTEGER, Filial_id INTEGER, Lote_id INTEGER)');
        DB::statement('CREATE TABLE Servico (id INTEGER PRIMARY KEY, status TEXT, data_inicio TEXT, data_fim TEXT NULL, custo REAL NULL, tipo TEXT, Veiculo_id INTEGER, Administrador_Funcionario_id INTEGER, Oficina_id INTEGER)');
        DB::statement('CREATE TABLE Aluguel (id INTEGER PRIMARY KEY AUTOINCREMENT, Atendente_entrega_id INTEGER, Atendente_devolucao_id INTEGER NULL, Pessoa_Fisica_id INTEGER NULL, status TEXT, valor REAL, tipo TEXT, data_inicial TEXT, data_final TEXT NULL, data_final_prevista TEXT, Contrato_frota_id INTEGER NULL, Veiculo_id INTEGER)');
        DB::statement('CREATE TABLE Venda (id INTEGER PRIMARY KEY, valor REAL, status TEXT, Veiculo_id INTEGER, Pessoa_Fisica_id INTEGER, Gerente_Comercial_Funcionario_id INTEGER)');
        DB::statement('CREATE TABLE Telefone_oficina (oficina_id INTEGER, telefone TEXT, PRIMARY KEY (oficina_id, telefone))');
        DB::statement('CREATE TABLE Telefone_montadora (montadora_id INTEGER, telefone TEXT, PRIMARY KEY (montadora_id, telefone))');
        DB::statement('CREATE TABLE Usuario_telefone (Usuario_id INTEGER, telefone TEXT, PRIMARY KEY (Usuario_id, telefone))');
        DB::statement('CREATE TABLE Cidade (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, Estado_sigla TEXT, UNIQUE (nome, Estado_sigla))');
        DB::statement('CREATE TABLE Bairro (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, Cidade_id INTEGER, UNIQUE (nome, Cidade_id))');
        DB::statement('CREATE TABLE Logradouro (id INTEGER PRIMARY KEY AUTOINCREMENT, nome TEXT, Bairro_id INTEGER, UNIQUE (nome, Bairro_id))');
        DB::statement('CREATE TABLE CEP (CEP TEXT PRIMARY KEY, Logradouro_id INTEGER)');
        DB::statement('CREATE TABLE Usuario_endereco (Usuario_id INTEGER PRIMARY KEY, numero TEXT NULL, complemento TEXT NULL, referencia TEXT NULL, CEP TEXT)');
        DB::statement('CREATE TABLE Oficina_endereco (id INTEGER PRIMARY KEY AUTOINCREMENT, numero TEXT NULL, complemento TEXT NULL, referencia TEXT NULL, Oficina_id INTEGER, CEP TEXT)');
        DB::statement('CREATE TABLE Montadora_endereco (id INTEGER PRIMARY KEY AUTOINCREMENT, Montadora_id INTEGER, numero TEXT NULL, complemento TEXT NULL, referencia TEXT NULL, CEP TEXT)');
        DB::statement('CREATE TABLE Filial_endereco (id INTEGER PRIMARY KEY AUTOINCREMENT, numero TEXT NULL, complemento TEXT NULL, referencia TEXT NULL, Filial_id INTEGER, CEP TEXT)');
        DB::statement('CREATE TABLE Filial_telefone (Filial_id INTEGER, telefone TEXT, PRIMARY KEY (Filial_id, telefone))');
    }

    private function seedBaseData(): void
    {
        DB::table('Usuario')->insert([
            ['id' => 1, 'username' => 'u1', 'nome' => 'Usuario 1', 'email' => 'u1@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
            ['id' => 2, 'username' => 'u2', 'nome' => 'Usuario 2', 'email' => 'u2@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
            ['id' => 10, 'username' => 'gerente', 'nome' => 'Gerente', 'email' => 'gerente@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
            ['id' => 20, 'username' => 'at1', 'nome' => 'Atendente 1', 'email' => 'at1@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
            ['id' => 21, 'username' => 'at2', 'nome' => 'Atendente 2', 'email' => 'at2@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
            ['id' => 30, 'username' => 'admin', 'nome' => 'Admin', 'email' => 'admin@example.com', 'ultimo_acesso' => null, 'data_cadastro' => '2026-01-01 00:00:00', 'ativo' => 1, 'senha_hash' => 'hash'],
        ]);
        DB::table('Filial')->insert(['id' => 1, 'nome' => 'Filial Centro']);
        DB::table('Montadora')->insert(['id' => 1, 'nome' => 'Montadora']);
        DB::table('Funcionario')->insert([
            ['Usuario_id' => 10, 'nome' => 'Gerente', 'sobrenome' => 'Teste', 'telefone' => '84999990000', 'Filial_id' => 1],
            ['Usuario_id' => 20, 'nome' => 'Atendente', 'sobrenome' => 'Um', 'telefone' => '84999990001', 'Filial_id' => 1],
            ['Usuario_id' => 21, 'nome' => 'Atendente', 'sobrenome' => 'Dois', 'telefone' => '84999990002', 'Filial_id' => 1],
            ['Usuario_id' => 30, 'nome' => 'Admin', 'sobrenome' => 'Teste', 'telefone' => '84999990003', 'Filial_id' => 1],
        ]);
        DB::table('Gerente_Comercial')->insert(['Funcionario_id' => 10]);
        DB::table('Atendente')->insert([['Funcionario_id' => 20], ['Funcionario_id' => 21]]);
        DB::table('Administrador')->insert(['Funcionario_id' => 30]);
        DB::table('Empresa')->insert(['Usuario_id' => 2, 'CNPJ' => '22345678000199']);
        DB::table('Lote')->insert(['id' => 1, 'Gerente_Comercial_Funcionario_id' => 10, 'Montadora_id' => 1, 'preco_total' => 100000, 'quantidade_veiculos' => 1]);
        DB::table('Estado')->insert(['sigla' => 'RN', 'nome' => 'Rio Grande do Norte']);
    }

    private function insertCnh(string $numero): void
    {
        DB::table('CNH')->insert([
            'numero' => $numero,
            'estado' => 'RN',
            'categoria' => 'B',
            'data_emissao' => '2020-01-01',
            'data_validade' => '2030-01-01',
        ]);
    }

    private function insertPessoaFisica(): void
    {
        $this->insertCnh('00012345678');
        DB::table('Pessoa_Fisica')->insert([
            'Cliente_id' => 1,
            'CPF' => '12345678901',
            'CNH_numero' => '00012345678',
        ]);
    }

    private function insertVeiculo(string $status): void
    {
        DB::table('Veiculo')->insert([
            'id' => 100,
            'status' => $status,
            'finalidade' => 'CURTA_DURACAO',
            'placa' => 'ABC1234',
            'grupo' => 'A',
            'quilometragem' => 1000,
            'Administrador_Funcionario_id' => 30,
            'Filial_id' => 1,
            'Lote_id' => 1,
        ]);
    }

    private function insertCep(): void
    {
        DB::table('Cidade')->insert(['id' => 1, 'nome' => 'Natal', 'Estado_sigla' => 'RN']);
        DB::table('Bairro')->insert(['id' => 1, 'nome' => 'Centro', 'Cidade_id' => 1]);
        DB::table('Logradouro')->insert(['id' => 1, 'nome' => 'Rua Principal', 'Bairro_id' => 1]);
        DB::table('CEP')->insert(['CEP' => '59000000', 'Logradouro_id' => 1]);
    }

    private function rentalPayload(array $overrides = []): array
    {
        return array_merge([
            'atendente_entrega_id' => 20,
            'atendente_devolucao_id' => null,
            'pessoa_fisica_id' => 1,
            'contrato_frota_id' => null,
            'status' => 'ATIVO',
            'tipo' => 'CURTA_DURACAO',
            'data_inicial' => '2026-01-01 08:00:00',
            'data_final' => null,
            'data_final_prevista' => '2026-01-01 12:00:00',
            'veiculo_id' => 100,
        ], $overrides);
    }

    /**
     * @param  list<string>  $roles
     * @return array<string, string>
     */
    private function authHeaders(array $roles, int $usuarioId = 999): array
    {
        $token = JWT::encode([
            'sub' => $usuarioId,
            'roles' => $roles,
            'iat' => time(),
            'exp' => time() + 3600,
        ], config('jwt.secret'), 'HS256');

        return ['Authorization' => 'Bearer '.$token];
    }
}
