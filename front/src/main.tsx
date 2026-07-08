import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
  ArrowLeft,
  BadgeCheck,
  CalendarDays,
  Car,
  Check,
  Clock,
  Gauge,
  LayoutDashboard,
  LogOut,
  MapPin,
  Pencil,
  Plus,
  Save,
  ShieldCheck,
  Trash2,
  User,
  X,
} from 'lucide-react';
import './styles.css';

const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

type Role = 'ADMINISTRADOR' | 'ATENDENTE' | 'GERENTE_COMERCIAL' | 'CLIENTE_PF' | 'EMPRESA';

type Session = {
  access_token: string;
  refresh_token: string;
  expires_in?: number;
  usuario?: Usuario;
  roles: Role[];
};

type Usuario = {
  id: number;
  username: string;
  nome: string;
  email: string;
};

type Filial = {
  id: number;
  nome: string;
};

type QuoteForm = {
  filial_retirada_id: number;
  filial_devolucao_id: number;
  data_retirada: string;
  data_devolucao: string;
};

type GrupoCotado = {
  grupo: string;
  disponiveis: number;
  menor_quilometragem: number;
  opcoes_quilometragem: MileageOption[];
};

type MileageOption = {
  tipo: 'ECONOMICA' | 'ILIMITADA';
  nome: string;
  franquia_km_por_dia: number | null;
  valor_km_excedente: number | null;
  base: number;
  total: number;
};

type Adicional = {
  id: string;
  nome: string;
  valor: number;
  por_dia: boolean;
};

type Cotacao = {
  filial_retirada: Filial;
  filial_devolucao: Filial;
  data_retirada: string;
  data_devolucao: string;
  tipo: string;
  adicionais_disponiveis: Adicional[];
  grupos: GrupoCotado[];
};

type ReservaConfirmada = {
  message: string;
  aluguel_id: number;
  total: number;
  veiculo_id: number;
  atendente_id: number;
};

type ApiError = Error & {
  status?: number;
  code?: string;
};

type View = 'reserva' | 'login' | 'perfil' | 'confirmar' | 'minhas-reservas' | 'backoffice';

type FieldType = 'text' | 'number' | 'date' | 'datetime-local' | 'select';

type FormField = {
  name: string;
  label: string;
  type?: FieldType;
  required?: boolean;
  options?: string[];
  placeholder?: string;
};

type BackofficeResource = {
  path: string;
  label: string;
  idKey: string;
  fields: FormField[];
  readOnly?: boolean;
};

function readSession(): Session | null {
  const raw = localStorage.getItem('locadora.session');
  return raw ? JSON.parse(raw) as Session : null;
}

function saveSession(session: Session | null) {
  if (session) {
    localStorage.setItem('locadora.session', JSON.stringify(session));
    return;
  }

  localStorage.removeItem('locadora.session');
}

function readAfterLoginView(): View | null {
  const value = localStorage.getItem('locadora.afterLoginView');
  return ['reserva', 'login', 'perfil', 'confirmar', 'minhas-reservas', 'backoffice'].includes(value ?? '')
    ? value as View
    : null;
}

function saveAfterLoginView(view: View | null) {
  if (view) {
    localStorage.setItem('locadora.afterLoginView', view);
    return;
  }

  localStorage.removeItem('locadora.afterLoginView');
}

async function api<T>(path: string, options: RequestInit = {}, session: Session | null = null): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...(session ? { Authorization: `Bearer ${session.access_token}` } : {}),
      ...(options.headers ?? {}),
    },
  });

  const text = await response.text();
  const body = text ? JSON.parse(text) : null;

  if (!response.ok) {
    const error = new Error(body?.message ?? 'Não foi possível concluir a operação.') as ApiError;
    error.status = response.status;
    error.code = body?.code;
    throw error;
  }

  return body as T;
}

function formatMoney(value: number) {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function toInputDateTime(value: string) {
  return value.replace(' ', 'T').slice(0, 16);
}

function fromInputDateTime(value: string) {
  return value.replace('T', ' ') + ':00';
}

function parseDateTime(value: string) {
  return new Date(value.replace(' ', 'T'));
}

function quoteDays(cotacao: Cotacao) {
  return Math.max(
    1,
    Math.ceil((parseDateTime(cotacao.data_devolucao).getTime() - parseDateTime(cotacao.data_retirada).getTime()) / 86400000),
  );
}

function hasCustomerProfile(session: Session | null) {
  return Boolean(session?.roles.includes('CLIENTE_PF'));
}

function hasStaffRole(session: Session | null) {
  return Boolean(session?.roles.some((role) => ['ADMINISTRADOR', 'ATENDENTE', 'GERENTE_COMERCIAL'].includes(role)));
}

function App() {
  const [session, setSessionState] = useState<Session | null>(() => readSession());
  const [view, setView] = useState<View>('reserva');
  const [afterLoginView, setAfterLoginViewState] = useState<View | null>(() => readAfterLoginView());
  const [filiais, setFiliais] = useState<Filial[]>([]);
  const [quoteForm, setQuoteForm] = useState<QuoteForm>({
    filial_retirada_id: 1,
    filial_devolucao_id: 1,
    data_retirada: '2026-07-08 11:00:00',
    data_devolucao: '2026-07-11 11:00:00',
  });
  const [cotacao, setCotacao] = useState<Cotacao | null>(null);
  const [selectedGroup, setSelectedGroup] = useState<GrupoCotado | null>(null);
  const [selectedMileage, setSelectedMileage] = useState<MileageOption | null>(null);
  const [selectedAddons, setSelectedAddons] = useState<string[]>([]);
  const [message, setMessage] = useState('');
  const [busy, setBusy] = useState(false);

  function setSession(next: Session | null) {
    saveSession(next);
    setSessionState(next);
  }

  function setAfterLoginView(next: View | null) {
    saveAfterLoginView(next);
    setAfterLoginViewState(next);
  }

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const accessToken = params.get('access_token');
    const refreshToken = params.get('refresh_token');

    if (window.location.pathname === '/auth/callback' && accessToken && refreshToken) {
      const next = { access_token: accessToken, refresh_token: refreshToken, roles: [] };
      setSession(next);
      void refreshMe(next).then((activeSession) => {
        setView(resolvePostLoginView(activeSession));
        setAfterLoginView(null);
      });
      window.history.replaceState({}, '', '/');
    }
  }, []);

  useEffect(() => {
    void api<Filial[]>('/api/public/filiais')
      .then((items) => {
        setFiliais(items);
        const first = items[0];
        if (first && quoteForm.filial_retirada_id === 1 && quoteForm.filial_devolucao_id === 1) {
          setQuoteForm((current) => ({
            ...current,
            filial_retirada_id: first.id,
            filial_devolucao_id: first.id,
          }));
        }
      })
      .catch((error) => setMessage((error as Error).message));
  }, []);

  const selectedTotal = useMemo(() => {
    if (!selectedMileage || !cotacao) {
      return 0;
    }

    const dias = quoteDays(cotacao);
    const addons = cotacao.adicionais_disponiveis
      .filter((item) => selectedAddons.includes(item.id))
      .reduce((sum, item) => sum + item.valor * (item.por_dia ? dias : 1), 0);

    return selectedMileage.total + addons;
  }, [cotacao, selectedAddons, selectedMileage]);

  async function refreshMe(activeSession = session) {
    if (!activeSession) {
      return null;
    }

    const me = await api<{ usuario: Usuario; roles: Role[] }>('/api/auth/me', {}, activeSession);
    const next = { ...activeSession, usuario: me.usuario, roles: me.roles };
    setSession(next);
    return next;
  }

  function resolvePostLoginView(activeSession: Session | null): View {
    if (afterLoginView === 'backoffice' && hasStaffRole(activeSession)) {
      return 'backoffice';
    }

    if (selectedGroup && selectedMileage) {
      return hasCustomerProfile(activeSession) ? 'confirmar' : 'perfil';
    }

    if (hasStaffRole(activeSession)) {
      return 'backoffice';
    }

    if (afterLoginView === 'minhas-reservas' && hasCustomerProfile(activeSession)) {
      return 'minhas-reservas';
    }

    if (hasCustomerProfile(activeSession)) {
      return 'minhas-reservas';
    }

    return 'perfil';
  }

  function navigate(next: View) {
    setMessage('');

    if ((next === 'backoffice' || next === 'minhas-reservas') && !session) {
      setAfterLoginView(next);
      setView('login');
      return;
    }

    setView(next);
  }

  async function requestQuote() {
    setMessage('');
    setBusy(true);
    try {
      const result = await api<Cotacao>('/api/public/cotacoes', {
        method: 'POST',
        body: JSON.stringify(quoteForm),
      });
      setCotacao(result);
      setSelectedGroup(null);
      setSelectedMileage(null);
      setSelectedAddons([]);
    } catch (error) {
      setMessage((error as Error).message);
    } finally {
      setBusy(false);
    }
  }

  async function confirmReservation() {
    if (!session || !selectedGroup || !selectedMileage) {
      setView('login');
      return;
    }

    setBusy(true);
    setMessage('');
    try {
      const activeSession = await refreshMe(session);
      await api<ReservaConfirmada>('/api/reservas', {
        method: 'POST',
        body: JSON.stringify({
          ...quoteForm,
          grupo: selectedGroup.grupo,
          quilometragem_tipo: selectedMileage.tipo,
          adicionais: selectedAddons,
          total_informado: selectedTotal,
        }),
      }, activeSession);
      setMessage('Reserva confirmada. O aluguel foi criado e o veículo foi marcado como alugado.');
      setView('minhas-reservas');
    } catch (error) {
      const apiError = error as ApiError;
      if (apiError.code === 'PESSOA_FISICA_REQUIRED') {
        setMessage('Complete seu cadastro de pessoa física para finalizar.');
        setView('perfil');
        return;
      }
      setMessage(apiError.message);
    } finally {
      setBusy(false);
    }
  }

  return (
    <main>
      <TopBar session={session} onNavigate={navigate} onLogout={() => {
        setAfterLoginView(null);
        setSession(null);
        setView('reserva');
      }} />
      {message && <div className="notice">{message}</div>}
      {view === 'reserva' && (
        <ReservationFlow
          session={session}
          filiais={filiais}
          form={quoteForm}
          cotacao={cotacao}
          selectedGroup={selectedGroup}
          selectedMileage={selectedMileage}
          selectedAddons={selectedAddons}
          selectedTotal={selectedTotal}
          onFormChange={setQuoteForm}
          loading={busy}
          onQuote={requestQuote}
          onGroup={setSelectedGroup}
          onMileage={setSelectedMileage}
          onToggleAddon={(id) => {
            setSelectedAddons((current) => current.includes(id)
              ? current.filter((item) => item !== id)
              : [...current, id]);
          }}
          onNeedLogin={() => {
            setAfterLoginView('confirmar');
            setView('login');
          }}
          onConfirm={() => {
            if (!session) {
              setAfterLoginView('confirmar');
              setView('login');
              return;
            }

            if (!hasCustomerProfile(session)) {
              setView('perfil');
              return;
            }

            setView('confirmar');
          }}
        />
      )}
      {view === 'login' && <LoginPanel onSession={async (next) => {
        setSession(next);
        const activeSession = await refreshMe(next);
        setView(resolvePostLoginView(activeSession));
        setAfterLoginView(null);
      }} />}
      {view === 'perfil' && <ProfilePanel session={session} onSaved={async () => {
        await refreshMe();
        setView(selectedGroup && selectedMileage ? 'confirmar' : 'reserva');
      }} />}
      {view === 'confirmar' && (
        <ConfirmReservation
          cotacao={cotacao}
          group={selectedGroup}
          mileage={selectedMileage}
          addons={cotacao?.adicionais_disponiveis.filter((item) => selectedAddons.includes(item.id)) ?? []}
          total={selectedTotal}
          loading={busy}
          onBack={() => setView('reserva')}
          onConfirm={confirmReservation}
        />
      )}
      {view === 'minhas-reservas' && <CustomerRentals session={session} />}
      {view === 'backoffice' && <Backoffice session={session} />}
    </main>
  );
}

function TopBar({ session, onNavigate, onLogout }: {
  session: Session | null;
  onNavigate: (view: View) => void;
  onLogout: () => void;
}) {
  return (
    <header className="topbar">
      <button className="brand" onClick={() => onNavigate('reserva')}>
        <Car size={28} />
        <span>Locadora IMD</span>
      </button>
      <nav>
        <button onClick={() => onNavigate('reserva')}>Reservar</button>
        <button onClick={() => onNavigate('minhas-reservas')}>Minhas reservas</button>
        {(!session || hasStaffRole(session)) && <button onClick={() => onNavigate('backoffice')}>Backoffice</button>}
        {session ? (
          <button className="icon-text" onClick={onLogout}><LogOut size={18} /> Sair</button>
        ) : (
          <button className="icon-text" onClick={() => onNavigate('login')}><User size={18} /> Login</button>
        )}
      </nav>
    </header>
  );
}

function ReservationFlow(props: {
  session: Session | null;
  filiais: Filial[];
  form: QuoteForm;
  cotacao: Cotacao | null;
  selectedGroup: GrupoCotado | null;
  selectedMileage: MileageOption | null;
  selectedAddons: string[];
  selectedTotal: number;
  loading: boolean;
  onFormChange: (form: QuoteForm) => void;
  onQuote: () => Promise<void>;
  onGroup: (group: GrupoCotado) => void;
  onMileage: (mileage: MileageOption) => void;
  onToggleAddon: (id: string) => void;
  onNeedLogin: () => void;
  onConfirm: () => void | Promise<void>;
}) {
  const step = props.selectedMileage ? 3 : props.selectedGroup ? 2 : props.cotacao ? 1 : 0;

  return (
    <>
      <section className="search-band">
        <div>
          <p className="eyebrow">Reserva online</p>
          <h1>Escolha o carro antes do login</h1>
        </div>
        <img src="/fleet.svg" alt="Frota Locadora IMD" />
        <form className="search-grid" onSubmit={(event) => {
          event.preventDefault();
          void props.onQuote();
        }}>
          <Field icon={<MapPin />} label="Retirada">
            <select
              value={props.form.filial_retirada_id}
              onChange={(event) => props.onFormChange({ ...props.form, filial_retirada_id: Number(event.target.value) })}
            >
              {props.filiais.map((filial) => <option key={filial.id} value={filial.id}>{filial.nome}</option>)}
            </select>
          </Field>
          <Field icon={<MapPin />} label="Devolução">
            <select
              value={props.form.filial_devolucao_id}
              onChange={(event) => props.onFormChange({ ...props.form, filial_devolucao_id: Number(event.target.value) })}
            >
              {props.filiais.map((filial) => <option key={filial.id} value={filial.id}>{filial.nome}</option>)}
            </select>
          </Field>
          <Field icon={<CalendarDays />} label="Data de retirada">
            <input
              type="datetime-local"
              value={toInputDateTime(props.form.data_retirada)}
              onChange={(event) => props.onFormChange({ ...props.form, data_retirada: fromInputDateTime(event.target.value) })}
            />
          </Field>
          <Field icon={<Clock />} label="Data de devolução">
            <input
              type="datetime-local"
              value={toInputDateTime(props.form.data_devolucao)}
              onChange={(event) => props.onFormChange({ ...props.form, data_devolucao: fromInputDateTime(event.target.value) })}
            />
          </Field>
          <button className="primary" disabled={props.loading || props.filiais.length === 0}>
            {props.loading ? 'Buscando...' : 'Continuar'}
          </button>
        </form>
      </section>

      <Stepper current={step} />

      <section className="content-with-summary">
        <div className="flow-column">
          {props.cotacao && !props.selectedGroup && (
            <GroupPicker cotacao={props.cotacao} onGroup={props.onGroup} />
          )}
          {props.cotacao && props.cotacao.grupos.length === 0 && (
            <EmptyState text="Nenhum grupo disponível para a filial e período selecionados." />
          )}
          {props.selectedGroup && !props.selectedMileage && (
            <MileagePicker group={props.selectedGroup} onMileage={props.onMileage} />
          )}
          {props.cotacao && props.selectedGroup && props.selectedMileage && (
            <AddonsPicker
              addons={props.cotacao.adicionais_disponiveis}
              selected={props.selectedAddons}
              onToggle={props.onToggleAddon}
            />
          )}
        </div>
        <ReservationSummary
          cotacao={props.cotacao}
          group={props.selectedGroup}
          mileage={props.selectedMileage}
          total={props.selectedTotal}
          signedIn={Boolean(props.session)}
          onNeedLogin={props.onNeedLogin}
          onConfirm={props.onConfirm}
        />
      </section>
    </>
  );
}

function Field({ icon, label, children }: {
  icon: React.ReactElement<{ size?: number }>;
  label: string;
  children: React.ReactNode;
}) {
  return (
    <label className="field">
      <span>{React.cloneElement(icon, { size: 18 })}{label}</span>
      {children}
    </label>
  );
}

function Stepper({ current }: { current: number }) {
  const steps = ['Local e data', 'Grupo', 'Tarifa', 'Login'];
  return (
    <ol className="stepper">
      {steps.map((step, index) => (
        <li className={index <= current ? 'active' : ''} key={step}>
          <span>{index < current ? <Check size={16} /> : index + 1}</span>
          {step}
        </li>
      ))}
    </ol>
  );
}

function GroupPicker({ cotacao, onGroup }: { cotacao: Cotacao; onGroup: (group: GrupoCotado) => void }) {
  return (
    <section>
      <h2>Escolha o grupo de carros</h2>
      <div className="cards-grid">
        {cotacao.grupos.map((group) => (
          <article className="vehicle-card" key={group.grupo}>
            <div className="vehicle-visual"><Car size={68} /></div>
            <h3>Grupo {group.grupo}</h3>
            <p>{group.disponiveis} veículo(s) disponível(is)</p>
            <strong>A partir de {formatMoney(group.opcoes_quilometragem[0].total)}</strong>
            <button className="primary" onClick={() => onGroup(group)}>Escolher grupo</button>
          </article>
        ))}
      </div>
    </section>
  );
}

function MileagePicker({ group, onMileage }: { group: GrupoCotado; onMileage: (mileage: MileageOption) => void }) {
  return (
    <section>
      <h2>O preço acompanha o tamanho da sua viagem</h2>
      <div className="cards-grid two">
        {group.opcoes_quilometragem.map((option) => (
          <article className="choice-card" key={option.tipo}>
            <Gauge size={28} />
            <h3>{option.nome}</h3>
            <p>
              {option.tipo === 'ECONOMICA'
                ? `${option.franquia_km_por_dia} km por dia, ${formatMoney(option.valor_km_excedente ?? 0)} por km excedente.`
                : 'Rode sem cobrança por quilômetro excedente.'}
            </p>
            <strong>{formatMoney(option.total)}</strong>
            <button className="primary" onClick={() => onMileage(option)}>Selecionar</button>
          </article>
        ))}
      </div>
    </section>
  );
}

function AddonsPicker({ addons, selected, onToggle }: {
  addons: Adicional[];
  selected: string[];
  onToggle: (id: string) => void;
}) {
  return (
    <section>
      <h2>Proteções e adicionais</h2>
      <div className="addons-list">
        {addons.map((addon) => (
          <label className="addon-row" key={addon.id}>
            <ShieldCheck size={24} />
            <span>
              <strong>{addon.nome}</strong>
              <small>{formatMoney(addon.valor)}{addon.por_dia ? ' / dia' : ' valor único'}</small>
            </span>
            <input type="checkbox" checked={selected.includes(addon.id)} onChange={() => onToggle(addon.id)} />
          </label>
        ))}
      </div>
    </section>
  );
}

function ReservationSummary(props: {
  cotacao: Cotacao | null;
  group: GrupoCotado | null;
  mileage: MileageOption | null;
  total: number;
  signedIn: boolean;
  onNeedLogin: () => void;
  onConfirm: () => void | Promise<void>;
}) {
  return (
    <aside className="summary">
      <h2>Resumo</h2>
      <dl>
        <dt>Retirada</dt>
        <dd>{props.cotacao?.data_retirada ?? 'Ainda não definida'}</dd>
        <dt>Devolução</dt>
        <dd>{props.cotacao?.data_devolucao ?? 'Ainda não definida'}</dd>
        <dt>Grupo</dt>
        <dd>{props.group ? `Grupo ${props.group.grupo}` : 'Selecione um grupo'}</dd>
        <dt>Tarifa</dt>
        <dd>{props.mileage?.nome ?? 'Selecione a quilometragem'}</dd>
      </dl>
      <div className="total">
        <small>Valor total previsto</small>
        <strong>{formatMoney(props.total)}</strong>
      </div>
      <button
        className="primary wide"
        disabled={!props.group || !props.mileage}
        onClick={() => props.signedIn ? void props.onConfirm() : props.onNeedLogin()}
      >
        {props.signedIn ? 'Confirmar reserva' : 'Entrar para finalizar'}
      </button>
    </aside>
  );
}

function LoginPanel({ onSession }: { onSession: (session: Session) => void }) {
  const [mode, setMode] = useState<'login' | 'cadastro'>('login');
  const [nome, setNome] = useState('');
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [login, setLogin] = useState('');
  const [senha, setSenha] = useState('');
  const [error, setError] = useState('');

  async function submit(event: FormEvent) {
    event.preventDefault();
    setError('');
    try {
      const next = await api<Session>('/api/auth/login', {
        method: 'POST',
        body: JSON.stringify({ login, senha }),
      });
      onSession(next);
    } catch (err) {
      setError((err as Error).message);
    }
  }

  async function register(event: FormEvent) {
    event.preventDefault();
    setError('');
    try {
      await api('/api/usuarios', {
        method: 'POST',
        body: JSON.stringify({
          nome,
          username,
          email,
          senha,
          ativo: true,
        }),
      });
      const next = await api<Session>('/api/auth/login', {
        method: 'POST',
        body: JSON.stringify({ login: username || email, senha }),
      });
      onSession(next);
    } catch (err) {
      setError((err as Error).message);
    }
  }

  return (
    <section className="auth-panel">
      <Car size={42} />
      <h1>Acesse sua conta</h1>
      <button className="oauth" onClick={() => { window.location.href = `${API_URL}/api/auth/google/redirect`; }}>
        <BadgeCheck size={20} /> Entrar com Google
      </button>
      <div className="segmented">
        <button className={mode === 'login' ? 'active' : ''} onClick={() => setMode('login')}>Login</button>
        <button className={mode === 'cadastro' ? 'active' : ''} onClick={() => setMode('cadastro')}>Cadastrar</button>
      </div>
      <form onSubmit={(event) => mode === 'login' ? void submit(event) : void register(event)}>
        {mode === 'cadastro' && (
          <>
            <label>Nome completo<input value={nome} onChange={(event) => setNome(event.target.value)} /></label>
            <label>Usuário<input value={username} onChange={(event) => setUsername(event.target.value)} /></label>
            <label>E-mail<input type="email" value={email} onChange={(event) => setEmail(event.target.value)} /></label>
          </>
        )}
        {mode === 'login' && <label>E-mail ou usuário<input value={login} onChange={(event) => setLogin(event.target.value)} /></label>}
        <label>Senha<input type="password" value={senha} onChange={(event) => setSenha(event.target.value)} /></label>
        {error && <p className="error">{error}</p>}
        <button className="primary wide">{mode === 'login' ? 'Entrar' : 'Criar conta e entrar'}</button>
      </form>
    </section>
  );
}

function ProfilePanel({ session, onSaved }: { session: Session | null; onSaved: () => Promise<void> }) {
  const [form, setForm] = useState({
    numero: '',
    estado: 'RN',
    categoria: 'B',
    data_emissao: '2020-01-01',
    data_validade: '2030-01-01',
    cpf: '',
  });
  const [error, setError] = useState('');

  if (!session) {
    return <EmptyState text="Entre para completar seu cadastro." />;
  }

  const activeSession = session;

  async function submit(event: FormEvent) {
    event.preventDefault();
    setError('');
    try {
      try {
        await api('/api/estados', {
          method: 'POST',
          body: JSON.stringify({
            sigla: form.estado,
            nome: form.estado === 'RN' ? 'Rio Grande do Norte' : form.estado,
          }),
        }, activeSession);
      } catch (estadoError) {
        if ((estadoError as ApiError).status !== 409) {
          throw estadoError;
        }
      }

      await api('/api/cnhs', {
        method: 'POST',
        body: JSON.stringify({
          numero: form.numero,
          estado: form.estado,
          categoria: form.categoria,
          data_emissao: form.data_emissao,
          data_validade: form.data_validade,
        }),
      }, activeSession);
      await api('/api/pessoas-fisicas', {
        method: 'POST',
        body: JSON.stringify({
          cliente_id: activeSession.usuario?.id ?? 0,
          cpf: form.cpf,
          cnh_numero: form.numero,
        }),
      }, activeSession);
      await onSaved();
    } catch (err) {
      setError((err as Error).message);
    }
  }

  return (
    <section className="form-panel">
      <h1>Complete seus dados para reservar</h1>
      <form onSubmit={(event) => void submit(event)}>
        <label>CPF<input value={form.cpf} onChange={(event) => setForm({ ...form, cpf: event.target.value })} /></label>
        <label>CNH<input value={form.numero} onChange={(event) => setForm({ ...form, numero: event.target.value })} /></label>
        <label>Estado<input value={form.estado} onChange={(event) => setForm({ ...form, estado: event.target.value })} /></label>
        <label>Categoria<input value={form.categoria} onChange={(event) => setForm({ ...form, categoria: event.target.value })} /></label>
        <label>Emissão<input type="date" value={form.data_emissao} onChange={(event) => setForm({ ...form, data_emissao: event.target.value })} /></label>
        <label>Validade<input type="date" value={form.data_validade} onChange={(event) => setForm({ ...form, data_validade: event.target.value })} /></label>
        {error && <p className="error">{error}</p>}
        <button className="primary">Salvar perfil</button>
      </form>
    </section>
  );
}

function CustomerRentals({ session }: { session: Session | null }) {
  const [items, setItems] = useState<Record<string, unknown>[]>([]);

  useEffect(() => {
    if (!session) {
      return;
    }
    void api<Record<string, unknown>[]>('/api/alugueis', {}, session)
      .then((rows) => setItems(rows.filter((item) => Number(item.Pessoa_Fisica_id) === session.usuario?.id)))
      .catch(() => setItems([]));
  }, [session]);

  if (!session) {
    return <EmptyState text="Entre para ver suas reservas." />;
  }

  return (
    <section className="table-panel">
      <h1>Minhas reservas</h1>
      <DataTable items={items} />
    </section>
  );
}

function Backoffice({ session }: { session: Session | null }) {
  const [resourcePath, setResourcePath] = useState('filiais');
  const [items, setItems] = useState<Record<string, unknown>[]>([]);
  const [editing, setEditing] = useState<Record<string, unknown> | null>(null);
  const [form, setForm] = useState<Record<string, string>>({});
  const [feedback, setFeedback] = useState('');
  const resources = backofficeResources(session);
  const resource = resources.find((item) => item.path === resourcePath) ?? resources[0];

  async function loadItems(activeResource = resource) {
    if (!session) {
      return;
    }

    try {
      const rows = await api<Record<string, unknown>[]>(`/api/${activeResource.path}`, {}, session);
      setItems(rows);
    } catch (error) {
      setItems([]);
      setFeedback((error as Error).message);
    }
  }

  useEffect(() => {
    setEditing(null);
    setForm(emptyForm(resource));
    setFeedback('');
    void loadItems(resource);
  }, [resourcePath, session]);

  if (!session) {
    return <EmptyState text="Entre para acessar a área interna." />;
  }

  if (!resource) {
    return <EmptyState text="Nenhum recurso disponível para este perfil." />;
  }

  const canWrite = !resource.readOnly;

  function selectResource(next: BackofficeResource) {
    setResourcePath(next.path);
  }

  function startCreate() {
    setEditing(null);
    setForm(emptyForm(resource));
    setFeedback('');
  }

  function startEdit(item: Record<string, unknown>) {
    setEditing(item);
    setForm(formFromItem(resource, item));
    setFeedback('');
  }

  async function submit(event: FormEvent) {
    event.preventDefault();

    if (!session || resource.readOnly) {
      return;
    }

    setFeedback('');
    const body = payloadFromForm(resource, form);
    const id = editing ? rowId(resource, editing) : null;
    const path = id === null ? `/api/${resource.path}` : `/api/${resource.path}/${id}`;

    try {
      await api(path, {
        method: id === null ? 'POST' : 'PUT',
        body: JSON.stringify(body),
      }, session);
      setFeedback(id === null ? 'Registro criado.' : 'Registro atualizado.');
      setEditing(null);
      setForm(emptyForm(resource));
      await loadItems(resource);
    } catch (error) {
      setFeedback((error as Error).message);
    }
  }

  async function remove(item: Record<string, unknown>) {
    if (!session || resource.readOnly) {
      return;
    }

    const id = rowId(resource, item);
    if (id === null || !window.confirm(`Excluir registro ${id}?`)) {
      return;
    }

    try {
      await api(`/api/${resource.path}/${id}`, { method: 'DELETE' }, session);
      setFeedback('Registro excluído.');
      await loadItems(resource);
    } catch (error) {
      setFeedback((error as Error).message);
    }
  }

  return (
    <section className="backoffice">
      <div className="side-nav">
        <LayoutDashboard size={24} />
        <strong>{session.roles.length ? session.roles.join(', ') : 'Sem perfil vinculado'}</strong>
        {resources.map((item) => (
          <button className={item.path === resource.path ? 'active' : ''} key={item.path} onClick={() => selectResource(item)}>
            {item.label}
          </button>
        ))}
      </div>
      <div className="table-panel">
        <div className="panel-heading">
          <div>
            <h1>{resource.label}</h1>
            <p className="muted">{canWrite ? 'Gerencie registros usando a API autenticada.' : 'Recurso somente leitura para este perfil.'}</p>
          </div>
          {canWrite && (
            <button className="primary" onClick={startCreate}>
              <Plus size={18} /> Novo
            </button>
          )}
        </div>

        {feedback && <p className="inline-notice">{feedback}</p>}

        {canWrite && (
          <form className="crud-form" onSubmit={(event) => void submit(event)}>
            {resource.fields.map((field) => (
              <label key={field.name}>
                {field.label}
                <BackofficeInput
                  field={field}
                  value={form[field.name] ?? ''}
                  onChange={(value) => setForm((current) => ({ ...current, [field.name]: value }))}
                />
              </label>
            ))}
            <div className="actions">
              <button className="secondary" type="button" onClick={startCreate}>
                <X size={18} /> Limpar
              </button>
              <button className="primary">
                <Save size={18} /> {editing ? 'Salvar edição' : 'Criar registro'}
              </button>
            </div>
          </form>
        )}

        <DataTable
          items={items}
          actions={canWrite ? {
            onEdit: startEdit,
            onDelete: (item) => void remove(item),
          } : undefined}
        />
      </div>
    </section>
  );
}

function BackofficeInput(props: {
  field: FormField;
  value: string;
  onChange: (value: string) => void;
}) {
  if (props.field.type === 'select') {
    return (
      <select
        required={props.field.required}
        value={props.value}
        onChange={(event) => props.onChange(event.target.value)}
      >
        <option value="">Selecione</option>
        {(props.field.options ?? []).map((option) => (
          <option key={option} value={option}>{option}</option>
        ))}
      </select>
    );
  }

  return (
    <input
      required={props.field.required}
      type={props.field.type ?? 'text'}
      step={props.field.type === 'number' ? 'any' : undefined}
      placeholder={props.field.placeholder}
      value={props.value}
      onChange={(event) => props.onChange(event.target.value)}
    />
  );
}

function backofficeResources(session: Session | null): BackofficeResource[] {
  const common: BackofficeResource[] = [
    resourceConfig('filiais', session),
    resourceConfig('veiculos', session),
    resourceConfig('alugueis', session),
    resourceConfig('relatorios/frota-disponivel', session),
    resourceConfig('relatorios/ocupacao-frota', session),
  ];

  if (!session) {
    return common;
  }

  if (session.roles.includes('ADMINISTRADOR')) {
    return [
      ...common,
      resourceConfig('usuarios', session),
      resourceConfig('montadoras', session),
      resourceConfig('oficinas', session),
      resourceConfig('funcionarios', session),
      resourceConfig('gerentes-comerciais', session),
      resourceConfig('administradores', session),
      resourceConfig('atendentes', session),
      resourceConfig('lotes', session),
      resourceConfig('servicos', session),
    ];
  }

  if (session.roles.includes('GERENTE_COMERCIAL')) {
    return [
      ...common,
      resourceConfig('contratos-frota', session),
      resourceConfig('vendas', session),
      resourceConfig('relatorios/contratos-frota', session),
    ];
  }

  if (session.roles.includes('ATENDENTE')) {
    return [
      ...common,
      resourceConfig('servicos', session),
      resourceConfig('relatorios/veiculos-manutencao', session),
    ];
  }

  return common;
}

function resourceConfig(path: string, session: Session | null): BackofficeResource {
  const admin = Boolean(session?.roles.includes('ADMINISTRADOR'));
  const gerente = Boolean(session?.roles.includes('GERENTE_COMERCIAL'));
  const atendente = Boolean(session?.roles.includes('ATENDENTE'));
  const report = path.startsWith('relatorios/');

  const writeableByAdmin = ['filiais', 'usuarios', 'montadoras', 'oficinas', 'funcionarios', 'gerentes-comerciais', 'administradores', 'atendentes', 'lotes', 'veiculos', 'servicos', 'alugueis'];
  const writeableByGerente = ['contratos-frota', 'vendas'];
  const writeableByAtendente = ['alugueis'];
  const canWrite = !report
    && ((admin && writeableByAdmin.includes(path))
      || (gerente && writeableByGerente.includes(path))
      || (atendente && writeableByAtendente.includes(path)));

  const configs: Record<string, Omit<BackofficeResource, 'readOnly'>> = {
    filiais: { path, label: 'Filiais', idKey: 'id', fields: [{ name: 'nome', label: 'Nome', required: true }] },
    usuarios: {
      path,
      label: 'Usuários',
      idKey: 'id',
      fields: [
        { name: 'username', label: 'Usuário', required: true },
        { name: 'nome', label: 'Nome', required: true },
        { name: 'email', label: 'E-mail', required: true },
        { name: 'senha', label: 'Senha', placeholder: 'Obrigatória ao criar; opcional ao editar' },
        { name: 'ativo', label: 'Ativo', type: 'select', required: true, options: ['1', '0'] },
      ],
    },
    montadoras: { path, label: 'Montadoras', idKey: 'id', fields: [{ name: 'nome', label: 'Nome', required: true }] },
    oficinas: { path, label: 'Oficinas', idKey: 'id', fields: [{ name: 'nome', label: 'Nome', required: true }] },
    funcionarios: {
      path,
      label: 'Funcionários',
      idKey: 'Usuario_id',
      fields: [
        { name: 'usuario_id', label: 'ID do usuário', type: 'number', required: true },
        { name: 'filial_id', label: 'ID da filial', type: 'number', required: true },
      ],
    },
    'gerentes-comerciais': { path, label: 'Gerentes comerciais', idKey: 'Funcionario_id', fields: [{ name: 'funcionario_id', label: 'ID do funcionário', type: 'number', required: true }] },
    administradores: { path, label: 'Administradores', idKey: 'Funcionario_id', fields: [{ name: 'funcionario_id', label: 'ID do funcionário', type: 'number', required: true }] },
    atendentes: { path, label: 'Atendentes', idKey: 'Funcionario_id', fields: [{ name: 'funcionario_id', label: 'ID do funcionário', type: 'number', required: true }] },
    lotes: {
      path,
      label: 'Lotes',
      idKey: 'id',
      fields: [
        { name: 'gerente_comercial_funcionario_id', label: 'ID gerente comercial', type: 'number', required: true },
        { name: 'montadora_id', label: 'ID montadora', type: 'number', required: true },
        { name: 'preco_total', label: 'Preço total', type: 'number', required: true },
        { name: 'quantidade_veiculos', label: 'Quantidade comprada', type: 'number', required: true },
      ],
    },
    veiculos: {
      path,
      label: 'Veículos',
      idKey: 'id',
      fields: [
        { name: 'status', label: 'Status', type: 'select', required: true, options: ['DISPONIVEL', 'ALUGADO', 'MANUTENCAO', 'VENDIDO'] },
        { name: 'finalidade', label: 'Finalidade', type: 'select', required: true, options: ['CURTA_DURACAO', 'LONGA_DURACAO'] },
        { name: 'placa', label: 'Placa', required: true },
        { name: 'grupo', label: 'Grupo', type: 'select', required: true, options: ['A', 'B', 'C', 'D'] },
        { name: 'quilometragem', label: 'Quilometragem', type: 'number', required: true },
        { name: 'administrador_cadastro_id', label: 'ID admin cadastro', type: 'number', required: true },
        { name: 'administrador_responsavel_id', label: 'ID admin responsável', type: 'number' },
        { name: 'filial_id', label: 'ID filial', type: 'number', required: true },
        { name: 'lote_id', label: 'ID lote', type: 'number', required: true },
      ],
    },
    servicos: {
      path,
      label: 'Serviços',
      idKey: 'id',
      fields: [
        { name: 'status', label: 'Status', required: true },
        { name: 'data_inicio', label: 'Data início', type: 'datetime-local', required: true },
        { name: 'data_fim', label: 'Data fim', type: 'datetime-local' },
        { name: 'custo', label: 'Custo', type: 'number' },
        { name: 'tipo', label: 'Tipo', required: true },
        { name: 'veiculo_id', label: 'ID veículo', type: 'number', required: true },
        { name: 'administrador_funcionario_id', label: 'ID administrador', type: 'number', required: true },
        { name: 'oficina_id', label: 'ID oficina', type: 'number', required: true },
      ],
    },
    'contratos-frota': {
      path,
      label: 'Contratos de frota',
      idKey: 'id',
      fields: [
        { name: 'gerente_comercial_id', label: 'ID gerente comercial', type: 'number', required: true },
        { name: 'empresa_id', label: 'ID empresa', type: 'number', required: true },
        { name: 'data_inicio', label: 'Data início', type: 'date', required: true },
        { name: 'data_final', label: 'Data final', type: 'date', required: true },
        { name: 'quantidade_veiculos', label: 'Quantidade de veículos', type: 'number', required: true },
      ],
    },
    vendas: {
      path,
      label: 'Vendas',
      idKey: 'id',
      fields: [
        { name: 'valor', label: 'Valor', type: 'number', required: true },
        { name: 'status', label: 'Status', required: true },
        { name: 'veiculo_id', label: 'ID veículo', type: 'number', required: true },
        { name: 'pessoa_fisica_id', label: 'ID pessoa física', type: 'number', required: true },
        { name: 'gerente_comercial_funcionario_id', label: 'ID gerente comercial', type: 'number', required: true },
      ],
    },
    alugueis: {
      path,
      label: 'Aluguéis',
      idKey: 'id',
      fields: [
        { name: 'atendente_entrega_id', label: 'ID atendente entrega', type: 'number', required: true },
        { name: 'atendente_devolucao_id', label: 'ID atendente devolução', type: 'number' },
        { name: 'pessoa_fisica_id', label: 'ID pessoa física', type: 'number' },
        { name: 'contrato_frota_id', label: 'ID contrato frota', type: 'number' },
        { name: 'status', label: 'Status', type: 'select', required: true, options: ['ABERTO', 'FINALIZADO', 'CANCELADO'] },
        { name: 'valor', label: 'Valor', type: 'number', required: true },
        { name: 'tipo', label: 'Tipo', type: 'select', required: true, options: ['PF', 'FROTA'] },
        { name: 'data_inicial', label: 'Data inicial', type: 'datetime-local', required: true },
        { name: 'data_final', label: 'Data final', type: 'datetime-local' },
        { name: 'data_final_prevista', label: 'Data final prevista', type: 'datetime-local', required: true },
        { name: 'veiculo_id', label: 'ID veículo', type: 'number', required: true },
      ],
    },
    'relatorios/frota-disponivel': { path, label: 'Frota disponível', idKey: 'id', fields: [] },
    'relatorios/ocupacao-frota': { path, label: 'Ocupação da frota', idKey: 'id', fields: [] },
    'relatorios/contratos-frota': { path, label: 'Relatório de contratos', idKey: 'id', fields: [] },
    'relatorios/veiculos-manutencao': { path, label: 'Manutenção', idKey: 'id', fields: [] },
  };

  return {
    ...(configs[path] ?? { path, label: path, idKey: 'id', fields: [] }),
    readOnly: !canWrite,
  };
}

function emptyForm(resource: BackofficeResource): Record<string, string> {
  return Object.fromEntries(resource.fields.map((field) => [field.name, '']));
}

const fieldAliases: Record<string, string> = {
  usuario_id: 'Usuario_id',
  filial_id: 'Filial_id',
  funcionario_id: 'Funcionario_id',
  gerente_comercial_funcionario_id: 'Gerente_Comercial_Funcionario_id',
  gerente_comercial_id: 'Gerente_Comercial_id',
  montadora_id: 'Montadora_id',
  administrador_cadastro_id: 'Administrador_cadastro_id',
  administrador_responsavel_id: 'Administrador_responsavel_id',
  veiculo_id: 'Veiculo_id',
  administrador_funcionario_id: 'Administrador_Funcionario_id',
  oficina_id: 'Oficina_id',
  empresa_id: 'Empresa_id',
  pessoa_fisica_id: 'Pessoa_Fisica_id',
  contrato_frota_id: 'Contrato_frota_id',
  atendente_entrega_id: 'Atendente_entrega_id',
  atendente_devolucao_id: 'Atendente_devolucao_id',
};

function valueFromItem(item: Record<string, unknown>, fieldName: string) {
  const key = fieldName in item ? fieldName : fieldAliases[fieldName];
  return key ? item[key] : undefined;
}

function formFromItem(resource: BackofficeResource, item: Record<string, unknown>): Record<string, string> {
  return Object.fromEntries(resource.fields.map((field) => {
    const value = valueFromItem(item, field.name);

    if (value === null || value === undefined) {
      return [field.name, ''];
    }

    if (field.type === 'datetime-local') {
      return [field.name, toInputDateTime(String(value))];
    }

    return [field.name, String(value)];
  }));
}

function payloadFromForm(resource: BackofficeResource, form: Record<string, string>) {
  return Object.fromEntries(resource.fields.map((field) => {
    const value = form[field.name] ?? '';

    if (value === '' && !field.required) {
      return [field.name, null];
    }

    if (field.type === 'number') {
      return [field.name, Number(value)];
    }

    if (field.type === 'datetime-local' && value) {
      return [field.name, fromInputDateTime(value)];
    }

    return [field.name, value];
  }));
}

function rowId(resource: BackofficeResource, item: Record<string, unknown>) {
  const value = item[resource.idKey];
  return value === null || value === undefined ? null : String(value);
}

function ConfirmReservation(props: {
  cotacao: Cotacao | null;
  group: GrupoCotado | null;
  mileage: MileageOption | null;
  addons: Adicional[];
  total: number;
  loading: boolean;
  onBack: () => void;
  onConfirm: () => Promise<void>;
}) {
  if (!props.cotacao || !props.group || !props.mileage) {
    return <EmptyState text="Escolha um grupo e uma tarifa antes de confirmar." />;
  }

  return (
    <section className="confirm-layout">
      <div className="table-panel">
        <h1>Confirme sua reserva</h1>
        <dl className="confirm-list">
          <dt>Retirada</dt>
          <dd>{props.cotacao.filial_retirada.nome} em {props.cotacao.data_retirada}</dd>
          <dt>Devolução</dt>
          <dd>{props.cotacao.filial_devolucao.nome} em {props.cotacao.data_devolucao}</dd>
          <dt>Grupo</dt>
          <dd>Grupo {props.group.grupo}</dd>
          <dt>Quilometragem</dt>
          <dd>{props.mileage.nome}</dd>
          <dt>Adicionais</dt>
          <dd>{props.addons.length ? props.addons.map((item) => item.nome).join(', ') : 'Nenhum adicional'}</dd>
        </dl>
        <div className="actions">
          <button className="secondary" onClick={props.onBack}>Voltar</button>
          <button className="primary" disabled={props.loading} onClick={() => void props.onConfirm()}>
            {props.loading ? 'Confirmando...' : 'Finalizar aluguel'}
          </button>
        </div>
      </div>
      <ReservationSummary
        cotacao={props.cotacao}
        group={props.group}
        mileage={props.mileage}
        total={props.total}
        signedIn
        onNeedLogin={props.onBack}
        onConfirm={props.onConfirm}
      />
    </section>
  );
}

function DataTable({ items, actions }: {
  items: Record<string, unknown>[];
  actions?: {
    onEdit: (item: Record<string, unknown>) => void;
    onDelete: (item: Record<string, unknown>) => void;
  };
}) {
  const keys = Object.keys(items[0] ?? {}).slice(0, 6);

  if (items.length === 0) {
    return <p className="muted">Nenhum registro retornado pela API.</p>;
  }

  return (
    <table>
      <thead>
        <tr>
          {keys.map((key) => <th key={key}>{key}</th>)}
          {actions && <th>Ações</th>}
        </tr>
      </thead>
      <tbody>
        {items.map((item, index) => (
          <tr key={index}>
            {keys.map((key) => <td key={key}>{String(item[key] ?? '')}</td>)}
            {actions && (
              <td>
                <div className="row-actions">
                  <button className="icon-button" title="Editar" onClick={() => actions.onEdit(item)}>
                    <Pencil size={16} />
                  </button>
                  <button className="icon-button danger" title="Excluir" onClick={() => actions.onDelete(item)}>
                    <Trash2 size={16} />
                  </button>
                </div>
              </td>
            )}
          </tr>
        ))}
      </tbody>
    </table>
  );
}

function EmptyState({ text }: { text: string }) {
  return (
    <section className="empty">
      <ArrowLeft size={28} />
      <p>{text}</p>
    </section>
  );
}

createRoot(document.getElementById('root')!).render(<App />);
