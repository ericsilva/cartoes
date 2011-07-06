<?php

/**
 * @category   Pagamento
 * @package    Cartao
 * @author     Eric Silva
 *
 */
require_once realpath(dirname(__FILE__)) . '/../Http.php';

abstract class CartaoCredito {

    /**
     * @var string $tid Sequencia identificadora da transacao junto a administradora do cartao
     */
    protected $tid;
    /**
     * @var string $afiliacao Identificacao do estabelecimento junto a administradora do cartao
     */
    protected $afiliacao;
    /**
     * @var double $valorTransacao Valor da transacao
     */
    protected $valorTransacao;
    /**
     * @var string $webService URL para comunicacao com o sistema da administradora do cartao
     */
    protected $webService;
    /**
     * @var Object Classe que representa a geração de log
     */
    protected $log;

    /**
     * Cada administradora possui o codigo de afiliacao com o formato diferenciado
     *
     * @param string $afiliacao
     */
    abstract public function setAfiliacao($afiliacao);

    /**
     * Cada administradora formata o valor de forma diferente
     *
     * @param double $valor
     */
    abstract public function setValorTransacao($valor);

    /**
     * Cada administradora formata o TID de forma diferente
     *
     * @param string $tid
     */
    abstract public function setTid($tid);

    abstract public function capturarTransacao();

    abstract public function gerarTid();

    /**
     * Construtor padrao, que pode receber o numero de afiliacao como argumento
     *
     * @param string $afiliacao numero de identificacao junto
     */
    public function __construct($afiliacao = null) {
        if ($afiliacao !== null)
            $this->setAfiliacao($afiliacao);
    }

    public function setWebservice($webService) {
        $this->webService = $webService;
        return $this;
    }

    public function getAfiliacao() {
        return $this->afiliacao;
    }

    public function getTid() {
        return $this->tid;
    }

    public function getValorTransacao() {
        return $this->valorTransacao;
    }

    public function getWebservice() {
        if (empty($this->webService))
            $this->setWebservice(Cielo::URL_PRODUCAO);
        return $this->webService;
    }

    public function setLog($log) {
        $this->log = $log;
        return $this;
    }

    public function getLog() {
        return $this->log;
    }

    protected function requisicao($url, $dados) {
        return Http::post($url, $dados);
    }

}