<?php 
/**
 * Classe com as funções para admin
 * @since 0.1
 */
class WoocommerceQSTIAdmin extends WoocommerceDisplayEbitBanner
{

    /**
     * Construtor da classe
     * @since 0.1
    */
    function __construct()
    {
        
    }

    /**
     * Função para adicionar link de configuração na listagem de plugins
     * @since 0.3
     */
    function wc_qsti_admin_config_link(array $actions, string $plugin_file, array $plugin_data, string $context) {
        $mylinks = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=wc_qsti' ) . '">'.__('Configurações', 'wc_qsti').'</a>',
        );
        $actions = array_merge( $actions, $mylinks );
        return $actions;
    }

    /**
     * Função para adicionar arquivos de css com styles
     * @since 0.3
     */
    function wc_qsti_css_styles_admin() {

        //Somente carregar na página determinada
        if (!isset($_GET['section']) || $_GET['section'] != 'wc_qsti') return;

        //Renderiza arquivo css
        wp_enqueue_style("wc-qsti-admin", plugins_url('', __DIR__).'/assets/css/wc-qsti-admin.css');
    }
        
    /**
     * Função requerimentos para inicializar o plugin
     * @since 0.1
    */
    function wc_qsti_require_woocommerce_plugin(){

        // Verify that CF7 is active and updated to the required version (currently 3.9.0)
        if ( is_plugin_active('woocommerce/woocommerce.php') ) {
            
            $wc_path = plugin_dir_path(WP_PLUGIN_DIR) . plugin_basename('plugins/woocommerce') . '/woocommerce.php';
            $wc_plugin_data = get_plugin_data( $wc_path, false, false);
            $wc_current_version = $wc_plugin_data['Version'];
            $wc_version = (int)preg_replace('/[.]/', '', $wc_current_version);

            // CF7 drops the ending ".0" for new major releases (e.g. Version 4.0 instead of 4.0.0...which would make the above version "40")
            // We need to make sure this value has a digit in the 100s place.
            if ( $wc_version < 100 ) {
                $wc_version = $wc_version * 10;
            }

            // If Woocommerce version is < 3.3.5
            if ( $wc_version < parent::$min_woocommerce_version ) {
                echo '<div class="error"><p><strong>'. __('Warning:', 'wc_qsti') . '</strong> '. __('Your Woocommerce version is: ','wc_qtsi') . $wc_current_version .  __('. O plugin '. WC_QSTI_PLUGIN_NAME .' requer que você tenha o plugin Woocommerce atualizado. A versão atual não é suportada.', 'wc_qsti') .'</p></div>';
            }

        }
        // If it's not installed and activated, throw an error
        else {
            echo '<div class="error"><p>' . __('O plugin '. WC_QSTI_PLUGIN_NAME .' requer que você tenha o plugin Woocommerce instalado e ativado. Por favor, vá para a administração de plugins e ative-o.', 'wc_qsti') .'</p></div>';
        }

        return $this->wc_qsti_load_order_query();
    }

    /**
     * Função que adiciona uma aba de administração no Woocommmerce
     * @since 0.1
     */
    function wc_qsti_admin_config($sections) {
        $sections['wc_qsti'] = __(WC_QSTI_PLUGIN_NAME, 'wc_qsti');
        return $sections;
    }

    /**
     * Função que adiciona uma aba de administração no Woocommmerce
     * @since 0.1
     */
    function wc_qsti_admin_config_settings($settings, $current_section) {

        if ($current_section == 'wc_qsti') {

            $settings_plugin = array();

            $doacao_html = (string) '<div class="plugin-about">
                    <div class="col-1">
                        <img src="'. plugins_url('', __DIR__).'/assets/img/qrcode-doacao.jpg" alt="QR Code PIX" style="max-width: 100px;margin-right: 20px;" />
                    </div>
                    <div class="col-2">'. _x("Obrigado por usar meu plugin! Se ele está sendo útil para sua loja considere em manter esse plugin realizando PIX de qualquer valor =)", "", "wp_aa"). '<br />
                    Ajude com um PIX para <strong>iranjosealves@gmail.com</strong><br /><br />
                    <span style="color: #999;"><small class="">'. _x("Procura um desenvolvedor para seu projeto? Veja meu portfólio de projetos >> ","","wp_aa") .' <a target="_blank" href="https://makingpie.com.br">makingpie.com.br</a></small></span></div>
                </div><!-- plugin-about -->';

            // Add Title to the Settings
            $settings_plugin[] = array( 
                'name' => __( 'Banner Ebit', 'wc_qsti' ), 
                'type' => 'title', 
                'desc' => __( $doacao_html .'Inicialmente você deve informar ID disponibilizado pelo Ebit para sua loja, parametro de retorno que cadastrou em seu gateway (Pagseguro, Bcash, etc). Após validar a integração, informe seu ID Buscapé.', 'wc_qsti' ), 
                'id' => 'wc_qsti' );


            // Add text field option
            $settings_plugin[] = array(
                'name'     => __( 'ID Ebit', 'wc_qsti' ),
                'desc_tip' => __( 'Seu Id fornecido pela Ebit', 'wc_qsti' ),
                'id'       =>  parent::$ebit_id,
                'default'  => __('', 'wc_qsti'),
                'type'     => 'number',
                'css'      => 'max-width: 200px;',
                'desc'     => __( 'ID disponiblizado para a sua loja ao cadastrar no Ebit.', 'wc_qsti' )
            );
            
            // Add text field option
            $settings_plugin[] = array(
                'name'     => __( 'Parametro de Transação', 'wc_qsti' ),
                'desc_tip' => __( 'Parametro de retorno definido em seu gateway', 'wc_qsti' ),
                'id'       =>  parent::$option_name,
                'default'  => __('', 'wc_qsti'),
                'type'     => 'text',
                'css'      => 'max-width: 200px;',
                'desc'     => __( 'Campo opcional - Parametro definido como retorno no seu gateway. Padrão: É utilizado parametro "key", usado como default pelo Woocommerce ao final do pedido.', 'wc_qsti' )
            );

            // Add text field option
            $settings_plugin[] = array(
                'name'     => __( 'ID Buscapé', 'wc_qsti' ),
                'desc_tip' => __( 'ID de sua loja no Buscapé', 'wc_qsti' ),
                'id'       =>  parent::$buscape_id,
                'default'  => __('', 'wc_qsti'),
                'type'     => 'text',
                'css'      => 'max-width: 200px;',
                'desc'     => __( 'Campo Opcional - Esse ID é disponibilizado após a integração do banner ocorrer.', 'wc_qsti' )
            );

            // Add select field option
            $settings_plugin[] = array(
                'name'     => __( 'Lightbox', 'wc_qsti' ),
                'desc_tip' => __( 'Exibir lightbox de avaliação de loja.', 'wc_qsti' ),
                'id'       =>  parent::$ebit_lightbox,
                'default'  => __('Não', 'wc_qsti'),
                'type'     => 'select',
                'options'   => array(
                    'false' => 'Não',
                    'true'  => 'Sim'
                ),
                'css'      => 'max-width: 200px;',
                'desc'     => __( 'Importante: Para lojas que ainda não confirmadas na plataforma Ebit, a opção "Não" deve ser selecionada.', 'wc_qsti' )
            );

            // Add select field option
            $settings_plugin[] = array(
                'name'     => __( 'Banner Responsivo', 'wc_qsti' ),
                'desc_tip' => __( 'Exibir banners com responsividade (se adequar ao tamanho do dispositivo).', 'wc_qsti' ),
                'id'       =>  parent::$responsive,
                'default'  => __('Não', 'wc_qsti'),
                'type'     => 'select',
                'options'   => array(
                    'false' => 'Não',
                    'true'  => 'Sim'
                ),
                'css'      => 'max-width: 200px;',
                'desc'     => __( 'Importante: Somente utilize essa opção se banners não estiverem sendo redimensionados automaticamente. Adiciona estilo css ao elemento de imagem afim de redimensiona-lo ao tamanho do dispositivo.', 'wc_qsti' )
            );

            // Add text field option
            $settings_plugin[] = array(
                'name'     => __( 'Banner Ebit', 'wc_qsti' ),
                'desc_tip' => __( 'Copie e cole na página de confirmação de compra ou na página definida como retorno.', 'wc_qsti' ),
                'id'       => 'shortcodes',
                'default'  => __('', 'wc_qsti'),
                'desc'     => '[wc_qsti_ebit_banner]',
                'type'     => 'textarea',
                'css'      => 'display:none;visibily:none;width:0px !important;',
            ); 
            
            // Add text field option
            $settings_plugin[] = array(
                'name'     => __( 'Selo Ebit', 'wc_qsti' ),
                'desc_tip' => __( 'Copie e cole, o selo deve ser inserido em todas as páginas, preferencialmente no rodapé.', 'wc_qsti' ),
                'id'       => 'shortcodes',
                'default'  => __('', 'wc_qsti'),
                'desc'     => '[wc_qsti_ebit_selo]',
                'type'     => 'textarea',
                'css'      => 'display:none;visibily:none;width:0px !important;',
            ); 

            $settings_plugin[] = array( 
                'type' => 'sectionend', 
                'id' => 'wc_qsti' );

            return $settings_plugin;
        
        }
        else{
            return $settings;
        }
        
    }

    /**
     * Função que salvas as configurações do plugin
     * @since 0.1
     */
    function wc_qsti_save_config() {

        /** Verifica se houve dados enviados via POST */
        if(!isset($_POST) || !array_key_exists( parent::$option_name, $_POST) || !array_key_exists(parent::$buscape_id, $_POST) || !array_key_exists(parent::$ebit_id, $_POST)){
            return true;
        }

        /** Filtra e adiciona o valor de config a variavel */
        $configData = filter_var_array($_POST, FILTER_SANITIZE_STRING);

        $update = [];

        /** Faz o update da configuração "BUSCAPE ID" no BD Wordpress */
        if( parent::wc_qsti_empty($configData[parent::$buscape_id])){
            $update['buscape_id'] = update_option( parent::$buscape_id, $configData[parent::$buscape_id]);
        }

        /** Faz o update da configuração "EBIT ID" no BD Wordpress */
        if( parent::wc_qsti_empty($configData[parent::$ebit_id])){
            $update['ebit_id'] = update_option( parent::$ebit_id, $configData[parent::$ebit_id]);
        }

        /** Faz o update da configuração "Parametro" no BD Wordpress */
        if( parent::wc_qsti_empty($configData[parent::$option_name])){
            $update['parameter'] = update_option( parent::$option_name, $configData[parent::$option_name]);
        }

        /** Faz o update da configuração "Lightbox" no BD Wordpress */
        if( parent::wc_qsti_empty($configData[parent::$ebit_lightbox])){
            $update['lightbox'] = update_option( parent::$ebit_lightbox, $configData[parent::$ebit_lightbox]);
        }

        /** Faz o update da configuração "Banner Responsivo" no BD Wordpress */
        if( parent::wc_qsti_empty($configData[parent::$responsive])){
            $update['responsive'] = update_option( parent::$responsive, $configData[parent::$responsive]);
        }

        /* Verifica se houve algum update com sucesso e retorna sucesso*/
        foreach($update as $key => $value){
            if($value == TRUE){
               return TRUE;
               break; 
            }
        }
        
        /** Retorna o resultado */
        return FALSE;
        
    }

    /**
     * Mostrar erros ao salvar
     * @since 0.1
     */
    function wc_qsti_save_error(){
        echo '<div class="error"><p>'.  __('Error in Saving', 'wc_qsti') .'</p></div>';
    }
}