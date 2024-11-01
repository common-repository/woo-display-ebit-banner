=== Iran Alves - Ebit Banner para Woocommerce  ===
Contributors: iranalves85
Tags: ebit, plugin ebit, banner ebit, avaliação ebit, avaliação de pedido, selo ebit, selo de avaliação, pagseguro integração ebit, integração ebit, mostrar ebit, reputação ebit, pedido selo, pedido avaliação, woocommerce extensão, woocommerce extension, extensão, plugin extensão.
Requires at least: 3.9.23
Tested up to: 5.7
Requires PHP: 5.6
Requires Woocommerce: 2.2+
Stable Tag: 0.2
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Donate Link: Pix para iranjosealves@gmail.com

Plugin que exibe banner ou selo Ebit com a utilização de shortcodes. Ebit é a maior plataforma de avaliação de lojas virtuais do Brasil. Mais informações: https://www.ebit.com.br/

== Description ==

Este plugin pode ser usado de duas formas, a primeira é inserir shortcode do plugin na página de finalização de pedido, normalmente na mesma página que contém o shortcode [woocommerce_checkout], quando da confirmação do pedido será exibido banner da Ebit. 
A segunda forma é através de uma página de redirecionamento, na qual também deve ser inserido shortcode do plugin, e informar nas configurações do plugin qual o parametro definido no gateway de pagamento (ex.: Pagseguro, MercadoPago, etc...). 

Importante: Testado primariamente com a versão 7.3 do PHP

*   Shortcode para exibir banner Ebit para avaliação de lojas.
*   Shortcode especifico para exibir selo obrigatório Ebit no rodapé ou em outro da loja.
*   Opção de habilitar ou não, Lightbox Ebit (Para efeito de maior conversão).

== Upgrade Notice == 

Nomes de arquivos foram alterados para entrar em conformidade com as diretrizes do Wordpress, portanto ao atualizar verifique se plugin continua ativado, se não estiver apenas ative novamente que os dados já registrados serão utilizados normalmente.

== Installation ==

* Instale e ative o plugin "Iran Alves - Ebit Banner para Woocommerce".
* Na aba "Produtos" nas configurações do Woocommerce, insira os dados obtidos ao se cadastrar na plataforma do Ebit, o campo principal obrigatório é "ID Ebit").
* Copie shortcode [wc_qsti_banner_ebit] e cole na página designada.
* Copie shortcode [wc_qsti_selo_ebit] e cole em local visivel na loja (recomendado em widget no rodapé)

== Frequently Asked Questions ==

= Eu encontrei um erro, como relatar ao desenvolvedor? =
Se encontrar erros no plugin, adicione um 'issues' no repositório Github: https://github.com/iranalves85/woocommerce-display-ebit-banner. Ou pode em enviar um email em iranjosealves@gmail.com, por favor coloque o assunto como "Iran Alves - Ebit Banner para Woocommerce". 
= Eu gostei do plugin! Como posso ajudar o desenvolvedor? =
Se você achou que o plugin lhe ajudou de alguma forma e gostaria de me pagar um café, realize um PIX para o email iranjosealves@gmail.com ou avalie o plugin no diretórios de plugin Wordpress, muito obrigado. WordPress is love!

== Screenshots ==

1. Página de configuração do plugin dentro do Woocommerce.

== Changelog ==

= 0.3 = 
* Alterado: Nome do plugin alterado para seguir as guidelines a respeito de marcas registradas e direitos autorais.
* Adicionado: Link "Configurações" na página de plugins
* Adicionado: Verificada completa compatibilidade à versão 5.7 do Wordpress.
* Adicionado: Verificada completa compatibilidade à versão 5.1.0 do Woocommerce.
* Adicionado: Opção de definir banner de forma responsiva.
* Corrigido: Opção "Parametro de Transação" não mais obrigatório.
* Corrigido: Forma de doação afim de manter manutenção do plugin ativo.

= 0.2 = 
* Adicionado: Se campo 'Parametro de Transação' não preenchido é utilizado os dados padrão do pedido 'Woocommerce' para exibir banner e Lightbox.
* Adicionado: Incluído nome do plugin nas mensagens de erros ao instalar ou ativar. 
* Corrigido: Erro PHP quando plugin é ativado sem plugin dependente 'Woocommerce' não está ativado ou instalado. Importante: A shortcode [wc_qsti_ebit_banner] deve estar inserido na página de Finalizar Compra juntamente com a shortcode [woocommerce_checkout].

= 0.1 =
* Criado

== Translations ==
* Português: Default!


== Credits ==
* Obrigado a comunidade e a equipe Wordpress por essa plataforma maravilhosa!