<?php 
/**
 * Classe com as funções para implementar banner Ebit
 * @since 0.1
 */
class WoocommerceDisplayEbitBanner
{
    
    static $min_woocommerce_version;
    
    //Parametros definidos pelo usuário
    static $option_name;
    static $buscape_id;
    static $ebit_id;
    static $ebit_lightbox;
    static $responsive;
    
    //Variaveis para reutilização
    static $savedData;
    static $productFieldsKeys;
    static $defaults;
    private $transactionId;

    /**
     * Função de construção da classe
     * @since 0.1
    */
    function __construct()
    {   
        //Menor versão Woocommerce suportada
        self::$min_woocommerce_version = (int) 220;
        self::$option_name      = 'wc_qsti_display_banner_ebit_config';
        self::$buscape_id       = 'wc_qsti_display_banner_buscape_id';
        self::$ebit_id          = 'wc_qsti_display_banner_ebit_id';
        self::$ebit_lightbox    = 'wc_qsti_display_banner_ebit_lightbox';
        self::$responsive       = 'wc_qsti_display_banner_responsive';
        self::$savedData        = $this->wc_qsti_load_var();

        //Nomes de keys a serem usados para encontrar os dados no wc_order
        self::$productFieldsKeys = array(
            'parcela'           => 'Parcelas',
            'meioPagamento'     => 'Tipo de pagamento',
            'prazo'             => 'method_title',
            'sku'               => array('ID' => 'product_id', 'variacao' => 'variation_id'),
            'produto'           => 'name',
            'quantidade'        => 'quantity',
            'valor'             => 'total',
        );

        /* Valores padrões da tag <param> Ebit*/
        self::$defaults = array(
            'email' => get_option('admin_email'), //(obrigatório)
            'gender' => '', //F | M
            'birthDay' => '', //DD-MM-AAAA
            'parcels' => '0', //Int
            'deliveryTax' => '0.00', //Decimal - Valor Frete (obrigatório)
            'deliveryTime' => '0', //Int - Tempo do Frete (obrigatório)
            'totalSpent' => '0.00',//Decimal - Valor Total (obrigatório)
            'value' => '0.00', //Decimal - Valor de cada produto (obrigatório)
            'quantity' => '0', //Int - qtd de cada produto (obrigatório)
            'productName' => 'Desconhecido', //Nome de cada produto (obrigatório)
            'transactionId' => 'XXXYYYZZZ',//ID da transação (obrigatório)
            'sku' => '000', //Código SKU de cada produto (obrigatório)
            'BuscapeId' => self::$savedData['buscape_id'],//
            'storeId' => self::$savedData['ebit_id']
        );

    }     

    /**
     * Função de inicialização
     * @since 0.1
    */
    public function init(){

        /** Instanciar classe de admin */
        $adminClass = new WoocommerceQSTIAdmin();

        /* Add a custom meta_data to query via Woocommerce Query */
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', array($this, 'wc_qsti_custom_query_var'), 10, 2 );
        
        /** Add a conifguration link in plugin list */
        $main_file = WC_QSTI_MAIN_FILENAME;
        add_filter("plugin_action_links_{$main_file}", array($adminClass, 'wc_qsti_admin_config_link'), 10, 4);
        
        /* Show notice if woocommerce not installed or disabled */
        add_action( 'admin_notices', array($adminClass, 'wc_qsti_require_woocommerce_plugin') );

        /* Add a new section in Woocommerce Admin */
        add_filter('woocommerce_get_sections_products', array($adminClass, 'wc_qsti_admin_config'), 10, 2);

        /* Add settings in Woocommerce Admin */
        add_filter('woocommerce_get_settings_products', array($adminClass, 'wc_qsti_admin_config_settings'), 1, 2);

        /* Salvamento de configurações */
        add_action('woocommerce_update_options_products', array($adminClass, 'wc_qsti_save_config'));

        /** Adicionar estilos css na pag de admin */
        add_action("admin_head", array($adminClass, "wc_qsti_css_styles_admin"));

        /* Add settings in Woocommerce Admin */
        add_shortcode('wc_qsti_ebit_banner', array($this, 'wc_qsti_show_ebit_banner'));

        /* Add settings in Woocommerce Admin */
        add_shortcode('wc_qsti_ebit_selo', array($this, 'wc_qsti_show_selo_banner'));
        
    }
    
    /**
     * Função para habilitar Woocommerce a retornar dados de pedidos baseado no parametro '_transaction_id'
     * @since 0.1
    */
    function wc_qsti_custom_query_var( $query, $query_vars ) {

        /** Valor armazenado nas configurações */
        $transaction_id = self::$savedData['transaction_id'];
        
        /** Registra parametro para executar querys */
        if ( ! empty( $query_vars[$transaction_id] ) ) {
            $query['meta_query'][] = array(
                'key'   => $transaction_id,
                'value' => esc_attr( $query_vars[$transaction_id] )
            );
        }

        return $query;
    }
    
    /**
     * Função para verificar variável
     * @since 0.1
    */
    public function wc_qsti_load_var(){
        
        $array = array();
        $array['transaction_id'] = get_option(self::$option_name, '');
        $array['lightbox'] = get_option(self::$ebit_lightbox, 'false');
        $array['buscape_id'] = get_option(self::$buscape_id, '');
        $array['ebit_id'] = get_option(self::$ebit_id, '');
        $array['responsive'] = get_option(self::$responsive, 'false');        
        
        return $array;
    }

    /**
     * Função para retornar dados da transação do Banco de Dados
     * Importante: Se parametro de transação não preenchido é exibido o banner utilizando os dados do pedido padrão do Woocommerce utilizando parametro GET key=wc_order_*
     * @version 0.2 - Suporte para métodos de pagamento padrões WC
     * @since 0.1
    */
    function wc_qsti_load_order_query(){

        //Verifica se função WC que retorna dados dos pedidos
        if (!function_exists('wc_get_order')) {
            $this->wc_qsti_register_error(__( WC_QSTI_PLUGIN_NAME . ' - Função "wc_get_orders" não existe. Provavelmente plugin Woocommerce desabilitado.', 'wc_qsti'), 'woocommerce_plugin_not_active');
        }

        //Retornar parametro do banco
        $parameterDefined = self::$savedData['transaction_id'];

        /** 
         * Retorna dados do pedido se houver parametro key na url (GET)
         * Padrão utilizado nas formas de pagamento nativas do Woocommerce
         * @since 0.2
        */
        $orderData = $this->wc_qsti_load_wc_order_by_key();

        /**
         * Se não foi definido um 'parametro de transação' nas configurações e $orderData retorno de objeto WC_Order
         * @since 0.2
         * */
        if (empty($parameterDefined) && $orderData != false && is_array($orderData)) {
            //Retorna o id da transação
            return $orderData;
        }

        //Atribui false a variavel
        $this->transactionId = false;
        //Pega parametro e valor da URL
        if (isset($_GET) && array_key_exists($parameterDefined, $_GET)) {
            //Definindo transacao direto da url
            $this->transactionId = filter_var($_GET[$parameterDefined], FILTER_SANITIZE_SPECIAL_CHARS);
        }

        if (!$this->transactionId) {
            //Retorna os dados do pedido pelo 'id'
            global $wp_query;
            
            //Retorna endpoint separado
            $endpointSplit = (function_exists(' wc_get_endpoint_url'))?
             preg_split('/\/+/', wc_get_endpoint_url('order-received')) : array();
            
            //Verifica se existe algum key com valor vazio
            $searchEmpty = array_search('', $endpointSplit );
            
            //Remove a key
            if($searchEmpty){
                unset($endpointSplit[$searchEmpty]);
            }

            //Aponta array para ultimo elemento e retorna
            $endpointVar = end($endpointSplit);

            //Se existir 'key' senão retorna false
            $orderData =  (is_array($endpointVar) && is_array($wp_query->query) && array_key_exists($endpointVar, $wp_query->query))? wc_get_order($wp_query->query[$endpointVar]) : false;
        }
        else{
            //Retorna os dados do pedido pelo 'código da transação'
            $orderData = wc_get_orders(array('_transaction_id' => $this->transactionId));
        }

        return $orderData;
    }


    /**
     * Retornando pedido através de get var 'key' em caso de método de pagamento nativo woocommerce
     * @since 0.2
     * */
    private function wc_qsti_load_wc_order_by_key() {
        
        //Array com dados do pedido ou FALSE
        $orderData = false;
        
        if (isset($_GET) && array_key_exists('key', $_GET)) {
            
            //Token chave do pedido
            $orderKey = $_GET['key'];

            $wcQuery = new WC_Order_Query(array('order_key' => $orderKey ));
            $orders =  $wcQuery->get_orders();
            
            //Definindo transacao direto da url
            if (is_array($orders) && count($orders) > 0) {
                //Atribui primeiro elemento do array
                $orderData = $orders;
            }
            
        }

        return $orderData;
    }
        

    /**
     * Função de shortcode para, finalmente, exibir o banner na página de redirecionamento
     * @since 0.1
    */    
    function wc_qsti_show_ebit_banner(){

        //Retorna dados da transação WC_Order
        $queryTransaction = $this->wc_qsti_load_order_query();
 
        //Finaliza função se resultado for false
        if($queryTransaction == false && !is_array($queryTransaction)){
            return $this->wc_qsti_register_error(__('Não foi encontrado nenhum pedido.', 'wc_qsti'), '_no_order_found');
        }

        //Finaliza função se resultado for false
        if(!self::$savedData['ebit_id']){
            return $this->wc_qsti_register_error(__('Seu id único Ebit não foi cadastrado. Por favor, vá ao ambiente de administração e cadastre.', 'wc_qsti'), '_no_ebit_id_registered');
        }

        //Retorna dados da objeto (query) do pedido em forma de array
        $order = (is_array($queryTransaction))? $queryTransaction[0]->get_data() : $queryTransaction->get_data();

        //Metadados do pedido
        $shippingMetadata = $order['meta_data'];
        //Dados de envio do pedido
        $shipping = $order['shipping_lines'];
        //Dados dos produtos do pedido
        $products = $order['line_items'];
        
        /**
         * Plugin Woocommerce Correios
         * Metódo para descobrir o prazo definido de envio para o pedido, já que valor não é armazenado de maneira convencional
         * */
        $order['deliveryTime'] = $this->wc_qsti_return_field_order_value_by_key($shipping, self::$productFieldsKeys['prazo'], false );

        /* Concatenar lista de propriedades dos produtos em string*/
        $order = array_merge($order, $this->wc_qsti_return_product_order_items_string_concatenated($products));

        /* Retorna quantidade de parcelas definidas no pedido */ 
        $order['orderParcels'] = $this->wc_qsti_return_parcels_order_by_key($shippingMetadata);

        /* Valores definidos através do pedido */
        $pedidosData = array(
            'email' => $order['billing']['email'], //(obrigatório)
            'zipCode' => str_replace('-', '', $order['billing']['postcode']), //
            'parcels' => $order['orderParcels'], //Int
            'deliveryTax' => $order['shipping_total'], //Decimal - Valor Frete (obrigatório)
            'deliveryTime' => $order['deliveryTime'], //Int - Tempo do Frete (obrigatório)
            'totalSpent' => $order['total'],//Decimal - Valor Total (obrigatório)
            'value' => $order['productValue'], //Decimal - Valor de cada produto (obrigatório)
            'quantity' => $order['productQtd'], //Int - qtd de cada produto (obrigatório)
            'productName' => $order['productNames'], //Nome de cada produto (obrigatório)
            'transactionId' => $order['id'],//ID da transação (obrigatório)
            'sku' => $order['productSku'] //Código SKU de cada produto (obrigatório)
        );

        /** Habilitando filtro para adicionar ou substituir parametros existentes */
        $pedidosData = array_merge($pedidosData, 
            apply_filters( 'wc_qsti_parameters_tag_filters', $pedidosData ));


        //Merge arrays
        $args = wp_parse_args( $pedidosData, self::$defaults );

        $html = '<param id="ebitParam" value="'; //Abre tag
        
        /* Percorre array para adicionar parametros e variavéis */ 
        $n = count($args);
        $i = 0;
        foreach($args as $key => $value){
            $html.= $key .'='.$value;
            if($i < $n ){
                $html.='&';
            }
            $i++;
        }

        $html .= '" />'; //Fecha tag
        
        $html .= '<a id="bannerEbit"></a>';

        $html .= '<script type="text/javascript" id="getSelo" src="https://imgs.ebit.com.br/ebitBR/selo-ebit/js/getSelo.js?'. self::$savedData['ebit_id'] .'&lightbox='. self::$savedData['lightbox'] .'"></script>';

        //Se for definido implementar banner responsivo
        if(!is_null(self::$savedData['responsive']) && self::$savedData['responsive'] == 'true'){
            add_action('wp_footer', array($this, 'wc_qsti_show_banner_responsive'));
        }

        return $html;
    }

    /**
     * Função que adiciona 
     * @since 0.3
     */
    function wc_qsti_show_banner_responsive() {
        //Renderiza arquivo css
        wp_enqueue_style("wc-qsti-responsive", plugins_url('', __DIR__).'/assets/css/wc-qsti-responsive.css');
    }

    /**
     * Função de shortcode para exibir selo Ebit (medalha)
     * @since 0.1
    */    
    function wc_qsti_show_selo_banner(){

        //Finaliza função se resultado for false
        if(!self::$savedData['ebit_id']){
            return $this->wc_qsti_register_error(__('Seu id único Ebit não foi cadastrado. Por favor, vá ao ambiente de administração e cadastre.', 'wc_qsti'), '_no_ebit_id_registered');
        }

        $html = '<a id="seloEbit" href="http://www.ebit.com.br/'. self::$savedData['ebit_id'] .'" target="_blank" data-noop="redir(this.href);"></a> <script type="text/javascript" id="getSelo" src="https://imgs.ebit.com.br/ebitBR/selo-ebit/js/getSelo.js?'. self::$savedData['ebit_id'] .'"> </script>';

        return $html;
    }

    /**
     * Função de retornar valor de acordo com key selecionada
     * @since 0.1
     */
     private function wc_qsti_return_field_order_value_by_key(array $array, string $stringKey, $isString = true )
     {
        
        $foundData = false; //Valor default

        foreach ($array as $key => $value) {
            //Verifica se array
            $methodData = ( is_array($value->get_data()) )? $value->get_data() : false;
            //Verifica se key existe
            if ( $methodData && array_key_exists($stringKey, $methodData) ) {
                //Armazena dado em variavel
                $foundData = filter_var($methodData[$stringKey], FILTER_SANITIZE_STRING);
                break;
            }
            else{
                continue;
            }
        }

        //Se resultado a retornar não for definido como string
        if ( !$isString) {
            
            //Expressão Regular para retornar prazo de envio
            $split = preg_match('/\s([0-9]+)\s/', $foundData, $matches); 

            if ( $split && count($matches) > 1) {
                $foundData = filter_var($matches[1], FILTER_SANITIZE_STRING);;                
            }
        }

        return $foundData;

     }

     /**
     * Função de concatenar dados de produtos de acordo com key selecionada
     * @since 0.1
     */
    private function wc_qsti_return_product_order_items_string_concatenated(array $productsArray )
    {
        //Keys válidas para produtos
        $array = array(
            'productNames'  => '',
            'productQtd'    => '',
            'productValue'  => '',
            'productSku'    => ''
        );

        /* Atribui cada valor ao array */
        foreach ($productsArray as $key => $value) {
            
            //Filtra os dados recebidos (nomes de produtos pode conter aspas)
            $productItem = filter_var_array( $value->get_data(), FILTER_SANITIZE_STRING );

            $array['productNames']  .= $productItem[self::$productFieldsKeys['produto']] . '|';
            $array['productQtd']    .= $productItem[self::$productFieldsKeys['quantidade']] . '|';
            $array['productValue']  .= $productItem[self::$productFieldsKeys['valor']] . '|';    
            
            //Aqui usamos uma query para retornar sku do produto e intercalar
            $array['productSku'] .= $this->wc_get_product_meta_data($productItem, '_sku') . '|';

        }

        return $array;
    }

    /** Função para retornar número de parcelas do pedido 
     * @since 0.1
    */
    private function wc_qsti_return_parcels_order_by_key($shippingMetadata) {
        
        $parcelQtd = '1'; //Valor 'default'

        //Percorre array de metadatas de entrega do pedido
        foreach ($shippingMetadata as $key => $value) {
            
            //Se existir uma chave com valor ao definido
            if( array_key_exists('key', $value->get_data()) 
                && $value->get_data()['key'] == self::$productFieldsKeys['parcela'] ){
                    $parcelQtd = $value->get_data()['value'];
                    break;
            }

        }

        return $parcelQtd;

    }

    /** Função para retornar metadados de produtos
     *  @since 0.1
     */
    function wc_get_product_meta_data($productData, $metadata) {
        
        //Retorna keys com valores de produtos
        $id  = self::$productFieldsKeys['sku']['ID'];
        $var = self::$productFieldsKeys['sku']['variacao'];

        /**
         * Atribui dados de metadata, se não houver usar o padrão woocommerce
         * @version 0.2 - Se não existir metadados de produto usa 'product_id'
         * @since 0.1
         * */
        $data = ($meta = get_post_meta( (int) $productData[$id], $metadata ))? $meta : $productData[$id];

        //Retorna os dados armazenados
        return (is_array($data))? $data[$productData[$var]] : $data;
    }

    /**
     * Função para registrar erros
     * @since 0.1
     */   
    public function wc_qsti_register_error( $stringError, $code = ''){
        $error = new WP_Error($code, $stringError );
        return false;
    }

    /**
     * Função para verificação de variaveis vazias
     * @since 0.1
     */   
    public function wc_qsti_empty($var){

        /** Se versão for maior que PHP 5.4 */
        if (function_exists('empty')) {
            return empty($var);
        }
        
        /** Se string for vazia */
        if (is_string($var)) {
            return $var == '';
        }

        /** Se numero for menor ou igual a zero */
        if($var <= 0){
            return true;
        } 
        
        /** Se array for menor ou igual a zero */
        if(count($var) <= 0){
            return true;
        } 

    }
    
}