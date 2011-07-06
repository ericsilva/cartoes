<?php

require_once realpath(dirname(__FILE__)) . '/CartaoCredito.php';

/**
 * @category   Pagamento
 * @package    Cartao
 * @subpackage Cielo
 * @author     Eric Silva
 *
 */
class Cielo extends CartaoCredito {

    /**
     * @var string $chave chave da loja junto a cielo
     */
    private $chave;
    /**
     * @var string $formaPagamento Débito, crédito à vista ou parcelado
     */
    private $formaPagamento;
    /**
     *
     * @var int $tipoParcelamento Loja ou administradora
     */
    private $tipoParcelamento;
    /**
     *
     * @var boolean realizar captura automatica
     */
    private $capturarAutomaticamente = false;
    /**
     *
     * @var int $autorizar Autorizar transação
     */
    private $autorizar = 2;
    /**
     *
     * @var string data da transacao
     */
    private $dataTransacao;
    /**
     *
     * @var string descricao da transacao
     */
    private $descricaoTransacao;
    /**
     *
     * @var string Nosso número
     */
    private $nossoNumero;
    /**
     *
     * @var string
     */
    private $idioma;
    /**
     *
     * @var string
     */
    private $urlRetorno;
    /**
     *
     * @var int
     */
    private $moeda;
    /**
     *
     * @var int
     */
    private $numeroParcelas;
    /**
     *
     * @var string
     */
    private $bandeira;
    /**
     *
     * @var string
     */
    private $urlAutenticacao;
    /**
     * Hash do número do cartão do portador.
     * @var string
     */
    private $pan;
    /**
     * @var int
     */
    private $status;
    /**
     * @var string
     */
    private $mensagemAutenticacao;
    /**
     * @var string
     */
    private $codigoAutenticacao;
    /**
     * @var string
     */
    private $mensagemAutorizacao;
    /**
     * @var string
     */
    private $codigoAutorizacao;
    /**
     * @var double
     */
    private $valorAutenticacao;
    /**
     * @var string
     */
    private $lr;
    /**
     * @var string
     */
    private $arp;
    /**
     *
     * @var string
     */
    protected $webService;

    const VERSAO = '1.1.0';
    const BANDEIRA_VISA = 'visa';
    const BANDEIRA_MASTER = 'mastercard';
    const BANDEIRA_ELO = 'elo';
    const ENCODING = 'iso-8859-1';
    const IDIOMA_PT = 'PT';
    const IDIOMA_EN = 'EN';
    const IDIOMA_ES = 'ES';
    const MOEDA_REAL = 986;
    const AUTORIZAR_SOMENTE_AUTENTICAR = 0;
    const AUTORIZAR_SOMENTE_SE_AUTENTICADA = 1;
    const AUTORIZAR_AUTENTICADA_NAO_AUTENTICADA = 2;
    const AUTORIZAR_DIRETO = 3;
    const PAGAMENTO_DEBITO = 'A';
    const PAGAMENTO_CREDITO = 1;
    const PARCELAMENTO_LOJA = 2;
    const PARCELAMENTO_ADMINISTRADORA = 3;
    const STATUS_AUTORIZADO = 4;
    const STATUS_CAPTURADO = 6;
    const URL_TESTE = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';
    const URL_PRODUCAO = 'https://ecommerce.cbmp.com.br/servicos/ecommwsec.do';


    private function gerarIdUnico() {
        return md5(date("YmdHisu"));
    }

    public function gerarTid() {
        return false;
    }

    public function setTid($tid) {
        $this->tid = $tid;
        return $this;
    }

    public function getTid() {
        return $this->tid;
    }

    public function setArp($arp) {
        $this->arp = $arp;
        return $this;
    }

    public function getArp() {
        return $this->arp;
    }

    public function setLr($lr) {
        $this->lr = $lr;
        return $this;
    }

    public function getLr() {
        return $this->lr;
    }

    /**
     *
     * @param string $chave chave de identificacao junto a cielo
     * @return Cielo
     */
    public function setChave($chave) {
        $this->chave = $chave;
        return $this;
    }

    /**
     *
     * @return string chave de identificacao junto a cielo
     */
    public function getChave() {
        return $this->chave;
    }

    /**
     *
     * @param double $valorAutenticacao
     * @return Cielo
     */
    private function setValorAutenticacao($valorAutenticacao) {
        $valorAutenticacao = substr($valorAutenticacao, 0, strlen($valorAutenticacao) - 2);
        $this->valorAutenticacao = number_format($valorAutenticacao, 2);
        return $this;
    }

    /**
     *
     * @return double
     */
    public function getValorAutenticacao() {
        return $this->valorAutenticacao;
    }

    /**
     *
     * @param string $mensagemAutenticacao
     * @return Cielo
     */
    public function setMensagemAutenticacao($mensagemAutenticacao) {
        $this->mensagemAutenticacao = $mensagemAutenticacao;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getMensagemAutenticacao() {
        return $this->mensagemAutenticacao;
    }

    /**
     *
     * @param string $mensagemAutorizacao
     * @return Cielo
     */
    public function setMensagemAutorizacao($mensagemAutorizacao) {
        $this->mensagemAutorizacao = $mensagemAutorizacao;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getMensagemAutorizacao() {
        return $this->mensagemAutorizacao;
    }

    /**
     *
     * @param int $codigoAutorizacao
     * @return Cielo
     */
    public function setCodigoAutorizacao($codigoAutorizacao) {
        $this->codigoAutorizacao = $codigoAutorizacao;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getCodigoAutorizacao() {
        return $this->codigoAutorizacao;
    }

    /**
     *
     * @param int $codigoAutenticacao
     * @return Cielo
     */
    public function setCodigoAutenticacao($codigoAutenticacao) {
        $this->codigoAutenticacao = $codigoAutenticacao;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getCodigoAutenticacao() {
        return $this->codigoAutenticacao;
    }

    /**
     *
     * @param int
     * @return Cielo
     */
    public function setStatus($status) {
        $this->status = (int) $status;
        return $this;
    }

    /**
     *
     * @return string chave de identificacao junto a cielo
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     *
     * @param string $pan Hash do número do cartão do portador
     * @return Cielo
     */
    public function setPan($pan) {
        $this->pan = $pan;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getPan() {
        return $this->pan;
    }

    /**
     *
     * @param string $urlAutenticacao
     * @return Cielo
     */
    public function setUrlAutenticacao($urlAutenticacao) {
        $this->urlAutenticacao = $urlAutenticacao;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUrlAutenticacao() {
        return $this->urlAutenticacao;
    }

    /**
     *
     * const PARCELAMENTO_LOJA
     * const PARCELAMENTO_ADMINISTRADORA
     *
     * @param int $tipoParcelamento
     * @return Cielo
     */
    public function setTipoParcelamento($tipoParcelamento) {
        $this->tipoParcelamento = (int) $tipoParcelamento;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getTipoParcelamento() {
        return $this->tipoParcelamento;
    }

    /**
     *
     * @param string $bandeira
     * @return Cielo
     */
    public function setBandeira($bandeira) {
        $this->bandeira = $bandeira;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getBandeira() {
        return $this->bandeira;
    }

    /**
     *
     * @param int $numeroParcelas Número de parcelas
     * @return Cielo
     */
    public function setNumeroParcelas($numeroParcelas) {
        $this->numeroParcelas = (int) $numeroParcelas;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getNumeroParcelas() {
        return $this->numeroParcelas;
    }

    /**
     *
     * Define se a transação será automaticamente capturada caso seja autorizada
     *
     * @param boolean $capturarAutomaticamente
     * @return Cielo
     */
    public function setCapturarAutomaticamente($capturarAutomaticamente) {
        $this->capturarAutomaticamente = (bool) $capturarAutomaticamente;
        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function getCapturarAutomaticamente() {
        return $this->capturarAutomaticamente;
    }

    /**
     *
     * Indicador de autorização automática:
     * 0 (não autorizar)
     * 1 (autorizar somente se autenticada)
     * 2 (autorizar autenticada e não-autenticada)
     * 3 (autorizar sem passar por autenticação ? válido somente para crédito)
     * Para Elo, o valor será sempre 3.
     *
     * @param int $autorizar
     * @return Cielo
     */
    public function setAutorizar($autorizar) {
        $this->autorizar = (int) $autorizar;
        return $this;
    }

    public function getAutorizar() {
        return $this->autorizar;
    }

    /**
     *
     * Código numérico da moeda na ISO 4217.
     * Para o Real, o código é 986.
     *
     * @param int $autorizar
     * @return Cielo
     */
    public function setMoeda($moeda) {
        $this->moeda = (int) $moeda;
        return $this;
    }

    public function getMoeda() {
        return $this->moeda;
    }

    /**
     *
     * URL da página de retorno. É para essa tela que o fluxo será retornado ao
     * fim da autenticação e/ou autorização.
     *
     * @param string $urlRetorno
     * @return Cielo
     */
    public function setUrlRetorno($urlRetorno) {
        $this->urlRetorno = $urlRetorno;
        return $this;
    }

    public function getUrlRetorno() {
        return $this->urlRetorno;
    }

    /**
     *
     * Usar constante
     *
     * @param string $idioma
     * @return Cielo
     */
    public function setIdioma($idioma) {
        $this->idioma = $idioma;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getIdioma() {
        return $this->idioma;
    }

    /**
     *
     * Descrição do pedido
     *
     * @param string $descricaoTransacao
     * @return Cielo
     */
    public function setDescricaoTransacao($descricaoTransacao) {
        $this->descricaoTransacao = html_entity_decode(substr(strip_tags($descricaoTransacao), 0, 1024));
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDescricaoTransacao() {
        return $this->descricaoTransacao;
    }

    /**
     *
     * Número do pedido
     *
     * @param string $nossoNumero
     * @return Cielo
     */
    public function setNossoNumero($nossoNumero) {
        $this->nossoNumero = $nossoNumero;
        return $this;
    }

    public function getNossoNumero() {
        return $this->nossoNumero;
    }

    /**
     *
     * Débito: const PAGAMENTO_DEBITO
     * Crédito à vista: 1
     * Parcelado: X, onde X é o número de parcelas
     *
     * @param string|int $formaPagamento
     * @return Cielo
     */
    public function setFormaPagamento($formaPagamento) {

        if (in_array($formaPagamento, array(self::PAGAMENTO_DEBITO, self::PAGAMENTO_CREDITO))) {
            $this->setNumeroParcelas(1);
        } elseif ($formaPagamento > 1) {
            $this->setNumeroParcelas($formaPagamento);
        }

        $this->formaPagamento = $formaPagamento;
        return $this;
    }

    /**
     * @return string codigo da forma de pagamento
     */
    public function getFormaPagamento() {
        return $this->formaPagamento;
    }

    /**
     *
     * @param double $valor
     * @return Cielo
     */
    public function setValorTransacao($valor) {
        $this->valorTransacao = number_format($valor, 2, '', '');
        return $this;
    }

    /**
     *
     * @param string $afiliacao numero de afilicao do cielo deve possui 10 digitos
     */
    public function setAfiliacao($afiliacao) {
        $afiliacao = trim($afiliacao);
        if (strlen($afiliacao) != 10)
            throw new Exception('Número de Afilicação inválido');
        $this->afiliacao = $afiliacao;
        return $this;
    }

    /**
     * Inicia o processo de pagamento
     * @return Cielo
     */
    public function realizarTransacao() {

        if (!$this->getValorTransacao())
            throw new Exception('O valor da transação não foi definido');

        if (!$this->getWebservice())
            throw new Exception('Endereço do webservice não está definido');

        if (in_array($this->getFormaPagamento(), array(self::PAGAMENTO_DEBITO, self::PAGAMENTO_CREDITO)))
            $this->setNumeroParcelas(1);

        if ($this->getNumeroParcelas() > 1 && null == $this->getTipoParcelamento())
            throw new Exception('Tipo de parcelamento não definido');

        if ($this->getNumeroParcelas() > 1)
            $this->setFormaPagamento($this->getNumeroParcelas());

        if (strlen($this->getUrlRetorno()) < 1)
            throw new Exception('URL de retorno não definida');

        $msg = ''
                . $this->xmlHeader()
                . $this->xmlRequisicaoTransacaoInicio()
                . $this->xmlDadosEc()
                . $this->xmlDadosPedido()
                . $this->xmlFormaPagamento()
                . $this->xmlUrlRetorno()
                . $this->xmlAutorizar()
                . $this->xmlCapturarAutomaticamente()
                . $this->xmlRequisicaoTransacaoFim();

        $resposta = $this->requisicao($this->getWebservice(), array('mensagem' => $msg), 'SOLICITACAO');

        if (null == $resposta || empty($resposta)) {
            throw new Exception('HTTP READ TIMEOUT - o Limite de Tempo da transação foi estourado');
        }

        if (stripos($resposta, 'SSL certificate problem') !== false) {
            throw new Exception('HTTP READ TIMEOUT - o Limite de Tempo da transação foi estourado');
        }

        $resposta = simplexml_load_string($resposta);

        if ($resposta->getName() == 'erro') {
            throw new Exception($resposta->mensagem);
        }

        $urlAutenticacao = "url-autenticacao";
        $this->setTid($resposta->tid);
        $this->setPan($resposta->pan);
        $this->setStatus($resposta->status);
        $this->setUrlAutenticacao($resposta->$urlAutenticacao);

        return $this;
    }

    /**
     *
     * @param string $tid
     * @return Cielo
     */
    public function consultarTransacao($tid) {

        $this->setTid($tid);

        $msg = ''
                . $this->xmlHeader()
                . $this->xmlRequisicaoConsultaInicio()
                . $this->xmlTid()
                . $this->xmlDadosEc()
                . $this->xmlRequisicaoConsultaFim();

        $resposta = $this->requisicao($this->getWebservice(), array('mensagem' => $msg), 'CONSULTA');

        if (null == $resposta || empty($resposta)) {
            throw new Exception('Erro na leitura');
        }

        $resposta = simplexml_load_string($resposta);

        if ($resposta->getName() == 'erro') {
            throw new Exception($resposta->mensagem);
        }

        $dadosPedido = 'dados-pedido';
        $formaPagamento = 'forma-pagamento';

        $this->setTid($resposta->tid);
        $this->setStatus($resposta->status);
        $this->setNossoNumero($resposta->$dadosPedido->numero);
        $this->setMoeda($resposta->$dadosPedido->moeda);
        $this->setDescricaoTransacao($resposta->$dadosPedido->descricao);
        $this->setIdioma($resposta->$dadosPedido->idioma);
        $this->setBandeira($resposta->$formaPagamento->bandeira);
        $this->setNumeroParcelas($resposta->$formaPagamento->parcelas);
        $this->setFormaPagamento($resposta->$formaPagamento->produto);
        $this->setValorTransacao($resposta->autorizacao->valor);
        $this->setValorAutenticacao($resposta->autenticacao->valor);
        $this->setCodigoAutenticacao($resposta->autenticacao->codigo);
        $this->setMensagemAutenticacao($resposta->autenticacao->mensagem);
        $this->setCodigoAutorizacao($resposta->autorizacao->codigo);
        $this->setMensagemAutorizacao($resposta->autorizacao->mensagem);
        $this->setLr($resposta->autorizacao->lr);
        $this->setArp($resposta->autorizacao->arp);

        return $this;
    }

    public function capturarTransacao() {
        return false;
    }

    private function xmlHeader() {
        return '<?xml version="1.0" encoding="' . self::ENCODING . '" ?>';
    }

    private function xmlRequisicaoTransacaoInicio() {
        $idTransacao = $this->gerarIdUnico();
        return '
        <requisicao-transacao id="' . $idTransacao . '" versao="' . self::VERSAO . '">';
    }

    private function xmlRequisicaoTransacaoFim() {
        return '
        </requisicao-transacao>';
    }

    private function xmlRequisicaoConsultaInicio() {
        return '
        <requisicao-consulta id="' . $this->gerarIdUnico() . '" versao="' . self::VERSAO . '">';
    }

    private function xmlRequisicaoConsultaFim() {
        return '
        </requisicao-consulta>';
    }

    private function xmlDadosEc() {
        return "
            <dados-ec>
                <numero>{$this->getAfiliacao()}</numero>
                <chave>{$this->getChave()}</chave>
            </dados-ec>";
    }

    private function xmlDadosPedido() {
        $this->dataTransacao = date("Y-m-d") . "T" . date("H:i:s");

        $info = "
            <dados-pedido>
                <numero>{$this->getNossoNumero()}</numero>
                <valor>{$this->getValorTransacao()}</valor>
                <moeda>{$this->getMoeda()}</moeda>
                <data-hora>{$this->dataTransacao}</data-hora>";

        $desc = $this->getDescricaoTransacao();
        if (!empty($desc)) {
            $info .= "
                <descricao>{$this->getDescricaoTransacao()}</descricao>";
        }

        $idioma = $this->getIdioma();
        if (empty($idioma)) {
            $this->setIdioma(self::IDIOMA_PT);
        }

        $info .= "
                <idioma>{$this->getIdioma()}</idioma>
            </dados-pedido>";

        return $info;
    }

    private function xmlFormaPagamento() {
        return "
            <forma-pagamento>
                <bandeira>{$this->getBandeira()}</bandeira>
                <produto>{$this->getFormaPagamento()}</produto>
                <parcelas>{$this->getNumeroParcelas()}</parcelas>
            </forma-pagamento>";
    }

    private function xmlAutorizar() {
        return "
            <autorizar>{$this->getAutorizar()}</autorizar>";
    }

    private function xmlUrlRetorno() {
        return "
            <url-retorno>{$this->getUrlRetorno()}</url-retorno>";
    }

    private function xmlTid() {
        return "
            <tid>{$this->getTid()}</tid>";
    }

    private function xmlCapturarAutomaticamente() {
        $capturar = ($this->getCapturarAutomaticamente()) ? "true" : "false";
        return "
            <capturar>{$capturar}</capturar>";
    }

    protected function requisicao($url, array $dados, $tipo) {

        $res = parent::requisicao($url, $dados);

        if(null !== $this->getLog()) {
            $this->getLog()->write("{$tipo}:\n" . end($dados));
            $this->getLog()->write("RESPOSTA:\n" . $res);
        }

        return $res;
    }

}