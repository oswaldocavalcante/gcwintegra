# GestãoClick

GestãoClick é um plugin para WordPress que integra o ERP GestãoClick ao WooCommerce.

## Instalação

1. Faça o download do plugin.
2. Extraia o conteúdo do arquivo zip.
3. Faça o upload da pasta `gestaoclick` para o diretório `wp-content/plugins/` do seu WordPress.
4. Ative o plugin através do menu 'Plugins' no WordPress.

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

- **Habilitar Orçamentos**: Ative o módulo de orçamentos para produtos sem controle de estoque no GestãoClick.
- **Quantidade Mínima**: Configure uma quantidade mínima de itens para um orçamento ser enviado ao GestãoClick.

## Contribuição

Se você deseja contribuir com o desenvolvimento do GestãoClick, siga os passos abaixo:

1. Faça um fork do repositório.
2. Crie uma nova branch (`git checkout -b feature/nova-funcionalidade`).
3. Faça as alterações necessárias e commit (`git commit -am 'Adiciona nova funcionalidade'`).
4. Envie para o repositório remoto (`git push origin feature/nova-funcionalidade`).
5. Abra um Pull Request.

## Autor

Desenvolvido por [Oswaldo Cavalcante](https://oswaldocavalcante.com/).

## Licença

Este projeto está licenciado sob a licença GPLv2 ou posterior. Leia a licença [LICENSE](http://www.gnu.org/licenses/gpl-2.0.html) para mais detalhes.