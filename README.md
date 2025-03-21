# GCWC - GestãoClick for WooCommerce

GCWC é um plugin para WordPress que integra o ERP GestãoClick ao WooCommerce.

- Author: [Oswaldo Cavalcante](https://oswaldocavalcante.com/)
- Doação: https://oswaldocavalcante.com/donation
- Licença: [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

## Funcionalidades

### Integração com API do GestãoClick

Permite configurar as credenciais de acesso à API do GestãoClick para sincronização de dados.

### Importação de Dados

- **Auto-importação**: Habilite a auto-importação para sincronizar periodicamente (a cada 15 minutos) o WooCommerce com o GestãoClick.
- **Seleção de Categorias**: Selecione as categorias para importar seus produtos do GestãoClick.
- **Produtos Proibidos**: Liste os códigos de produtos que não devem ser importados do GestãoClick.

### Exportação de Dados

- **Auto-exportação de Vendas**: Habilite a auto-exportação para enviar novas vendas pagas e seus respectivos clientes ao GestãoClick.
- **Situação Padrão**: Defina a situação padrão para novas vendas exportadas para o GestãoClick.
- **Transportadora Padrão**: Selecione a transportadora padrão para novas vendas exportadas ao GestãoClick.

### Calculadora de Frete

Habilite a calculadora de frete para aparecer na página individual de produtos e orçamento.

### Orçamentos

Módulo adicional de orçamentos para produtos sem controle de estoque no GestãoClick.

## Instalação

1. Vá em WooCommerce -> Configurações -> Integração -> GestãoClick e insira suas credenciais de acesso à API obtidas em [https://gestaoclick.com/integracao_api/configuracoes/gerar_token](https://gestaoclick.com/integracao_api/configuracoes/gerar_token).
2. Selecione as categorias que deseja importar, e habilite a auto importação ou faça uma importação manual.

## Contribuição

Se você deseja contribuir com o desenvolvimento do GestãoClick, siga os passos abaixo:

1. Faça um fork do repositório.
2. Crie uma nova branch (`git checkout -b feature/nova-funcionalidade`).
3. Faça as alterações necessárias e commit (`git commit -am 'Adiciona nova funcionalidade'`).
4. Envie para o repositório remoto (`git push origin feature/nova-funcionalidade`).
5. Abra um Pull Request.