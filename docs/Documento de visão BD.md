**Documento de Visão do Produto**

**1\. Descrição da Visão do Produto**  
Esta seção descreve o propósito do sistema e os benefícios esperados para a organização.

### **1.1 Objetivos e Propósito**

O presente projeto tem como objetivo desenvolver um Banco de Dados para um Sistema de Gestão Inteligente para aluguel de veículos, com o intuito de otimizar tempo, recursos e pessoas nos processos cotidianos de locadoras veiculares. O propósito central é viabilizar um modelo de negócio ágil e tecnologicamente avançado.

### **1.2 Motivação e Estado Atual**

* **Situação Atual:** O mercado de locação tradicional é engessado em diárias mínimas de 48 horas.  
* **Problema Identificado:** Clientes que necessitam de veículos por curtos períodos são forçados a pagar por tempo não utilizado, gerando ineficiência financeira para o usuário, subutilização da frota para a locadora e perda de locatários.  
* **Melhorias Esperadas**: Introdução da micro locação fracionada, reduzindo o tempo de ociosidade dos veículos e captando uma nova fatia de mercado (uso urbano de curto prazo).

### **1.3 Inovação**

O sistema propõe uma locação diária flexível com duração mínima entre 4 e 6 horas, associada a uma precificação dinâmica que ajusta valores conforme a demanda, categoria do veículo e disponibilidade da frota em tempo real.

## **2\. Descrição dos Atores Envolvidos e dos Usuários Finais**

**2.1 Envolvidos**

| Nome | Descrição | Responsabilidade |
| :---- | :---- | :---- |
| Locadora  | Entidade jurídica proprietária da plataforma e da frota | Definir as regras de negócio, garantir a viabilidade financeira e validar a inovação proposta |
| Gerente Comercial | Funcionário responsável por operações comerciais  | Garantir a retenção do cliente e confiabilidade do sistema  |
| Funcionário/atendente  | Funcionário da locadora  | Responsável pela confirmação de entrega/devolução do veículo  |
| Montadora de veículos  | Fabricante de automóveis  | Venda de veículos para a locadora. |
| Oficina Mecânica | Oficina para reparo de veículos (da própria locadora ou não) | Manutenção e vistoria de veículos para venda, aluguel ou reparo. |

				**Tabela 1.1 \- Envolvidos**  
**2.2 Usuários finais**

| Nome | Descrição | Responsabilidade | Envolvido Representante |
| :---- | :---- | :---- | :---- |
| Locatário (Pessoa física) | Indivíduo interessado em alugar um veículo de terceiros por curto prazo | Realizar reservas, efetuar pagamentos e avaliar o veículo | Funcionário/atendente da locadora |
| Empresa (CNPJ) | Empresa interessada em terceirizar sua frota de veículos | Atribuir veículos alugados aos seu funcionários, negociar os a terceirização de frota e monitorar por meio do sistema multas e outros dados dos motoristas dos seus veículos terceirizados | Gerente Comercial |
| Administrador | Funcionário gestor do sistema da locadora | Configurar algoritmos de preço e gerir a frota.  | Locadora |

				  
**Tabela 1.2 \- Usuários finais**  
				

## **3\. Descrição do Ambiente do Usuário:**

O sistema será utilizado por clientes (pessoa física) e funcionários da locadora, em ambiente digital (web) e presencial.  
O processo envolve etapas como cotação, envio de documentos, pagamento, retirada e devolução do veículo, podendo durar de algumas horas a vários dias. A quantidade de usuários varia conforme a demanda.  
Atualmente, há restrições como processos manuais, validação de documentos e necessidade de atendimento presencial.  
O software será acessado via navegador em computadores e dispositivos móveis, podendo integrar-se a serviços externos, como meios de pagamento e banco de dados da locadora.

## **4\. Principais Necessidades dos Usuários e Envolvidos:**

Esta seção descreve as soluções para necessidades e preocupações dos usuários e envolvidos.

| Interessado | Necessidade | Prioridade | Preocupações | Solução atual | Solução proposta |
| :---- | :---- | :---- | :---- | :---- | :---- |
| Locatário (PF) | Alugar um veículo por menos de 48 horas. | Alta | Inviabilidade de alugar algum veículo por menos de 48 horas | O usuário paga 48h ou utiliza transporte por app. | Diária flexível com duração mínima entre 4 e 6 horas. |
| Locadora  | Segurança e agilidade em processos de locação | Alta | Vulnerabilidade a furto de veículos em prol da velocidade e comodidade do cliente | Check-in e agendamento online com retirada rápida de veículo sem validação ou verificação humana | Cadastro e agendamento online com validação presencial de CNH na hora de retirada do veículo (no pátio) |
| Locadora | Otimização de frota | Média | Veículos parados no pátio entre locações deixam de gerar renda. | Veículo fica ocioso entre locações perdendo potencial rentabilidade. | Diária flexível com duração mínima pode preencher os intervalos, assim gerando renda. |
| Empresa (CNPJ) | Gerenciamento de multas e demandas de frotas terceirizadas | Média | Falta de controle sobre uso indevido, precisando se responsabilizar pela má conduta de funcionários | A empresa faz esse controle ou é disponibilizado um sistema de gerenciamento | O sistema disponibiliza uma interface para gerenciamento dessa frota terceirizada |

(Ignore o item 5)
## **5\. Alternativas e Concorrência:**

**5.1** **Concorrentes Diretos:** Grandes locadoras com tradição no mercado.  
Pontos fortes: alta confiabilidade, processos digitais ágeis (Check-in Express) e frotas imensas.  
Pontos fracos: Rigidez do modelo de negócio. O sistema de banco de dados deles é otimizado para diárias de 24h. Se você quiser um carro por 5 horas, eles até alugam, mas cobram 24h. A "solução local" ou o status quo deles não atende à necessidade de economia em trajetos curtos.  
**5.2 Concorrentes Indiretos:** Apps de Transporte (Uber, 99)Pontos Fortes: Baixo custo para viagens únicas e zero burocracia.Pontos Fracos (A sua brecha): Falta de autonomia e privacidade. O Uber é ineficiente para o usuário que precisa fazer 5 paradas em 4 horas ou carregar muitas compras/equipamentos, pois o custo de espera e as múltiplas chamadas tornam a experiência estressante e cara.

## **6\. Visão Geral do Produto:** 

**6.1 Perspectiva do Produto**  
O sistema consiste em uma plataforma web para gestão de locação de veículos, acessível por clientes e funcionários. Ele centraliza todo o processo de locação, desde a cotação até a devolução.  
O produto funciona como um sistema integrado, conectando:

* interface do cliente (App/Portal);  
* sistema interno da locadora (regras, frota, contratos);  
* módulo operacional (atendimento presencial);  
* serviços externos, como meios de pagamento.

Dessa forma, o sistema automatiza e organiza o fluxo de locação, reduzindo processos manuais.

**6.2 Suposições e Dependências**  
**Suposições:**

* Usuários possuem acesso à internet;  
* Clientes fornecem documentos válidos;  
* Regras de negócio (ex: tempo mínimo e precificação) permanecem estáveis.

**Dependências:**

* Serviços de pagamento;  
* Infraestrutura do sistema (servidor e banco de dados);  
* Equipe operacional da locadora.

## **7\. Recursos do Produto:** 

| ID | Requisito funcional | Descrição | Prioridade | Benefício | Esforço | Risco |
| :---- | :---- | :---- | :---- | :---- | :---- | :---- |
| RF01 | Solicitação de Locação | Permite ao cliente informar período, local e categoria do veículo para iniciar a cotação. | Alta | Alto | Baixo  | Baixo |
| RF02 | Cotação e Modalidade | Calcula valores e apresenta opções (tradicional ou flexível) com base em regras e disponibilidade. | Alta | Alto | Médio | Médio |
| RF03 | Gestão de Documentos | Permite envio, validação e reenvio de documentos do cliente (CNH e comprovante). | Alta | Alto | Médio | Médio |
| RF04 | Pagamento da Reserva | Realiza o pagamento do sinal para confirmação da reserva via integração externa. | Alta | Alto | Médio | Alto |
| RF05 | Gestão da Reserva | Controla o status da reserva até sua confirmação final. | Alta | Alto | Baixo | Baixo |
| RF06 | Preparação e Entrega | Permite ao atendente preparar o veículo e registrar o check-in e entrega. | Média | Médio | Baixo | Baixo |
| RF07 | Registro de Uso | Acompanha o período de uso do veículo conforme contrato. | Média | Médio | Baixo | Baixo |
| RF08 | Devolução e Vistoria | Registra a devolução, vistoria e condições do veículo. | Alta | Alto | Médio | Médio |
| RF09 | Ajustes Financeiros | Calcula valores adicionais (ex: horas extras, combustível, avarias). | Alta | Alto | Médio | Médio |
|  RF10 | Encerramento do Contrato | Permite pagamento final e finalização da locação. | Alta | Alto | Baixo | Baixo |

## **8\. Outros Requisitos do Sistema:**

O sistema deverá seguir padrões web modernos (arquitetura cliente-servidor, API REST) e ser compatível com navegadores atuais e dispositivos móveis, operando com banco de dados relacional.

Em termos de desempenho, deve responder rapidamente às operações principais (cotação, reserva, validação e pagamento), suportando múltiplos usuários simultâneos e garantindo consistência nas transações.

Quanto à qualidade, o sistema deve apresentar boa usabilidade, robustez no tratamento de erros (como falhas no envio de documentos ou pagamento) e tolerância a inconsistências, permitindo reprocessamento.

Do ponto de vista legal, o sistema deve estar em conformidade com a Lei Geral de Proteção de Dados (LGPD), garantindo:

* coleta mínima de dados pessoais;  
* consentimento do usuário;  
* armazenamento seguro de informações (como CNH e comprovantes);  
* controle de acesso e proteção contra vazamentos.

Existem restrições de negócio e design, como a obrigatoriedade de validação de documentos, aplicação de políticas dinâmicas de tempo mínimo (4 a 6 horas) e dependência de serviços externos (pagamentos).

O sistema deve fornecer documentação básica (manual do usuário e ajuda online). Esses requisitos possuem alta prioridade, com alta estabilidade, alto benefício, esforço moderado e risco médio devido à integração com serviços externos e tratamento de dados sensíveis.




