<?php
require_once realpath(dirname(__FILE__)) . '/CartaoCredito.php';
require_once realpath(dirname(__FILE__)) . '/../Http.php';

/**
 * @category   Pagamento
 * @package    Cartao
 * @subpackage Visa
 * @author     Eric Silva
 *
 */
class Visa extends CartaoCredito {

    /**
     * @var string $merchid nome da loja junto a visanet
     */
    private $merchid;

    /**
     * @var string $formaPagamento
     */
    private $formaPagamento;

    /**
     * @var string $webService
     */
    protected $webService = 'http://cartao.itarget.com.br/componentes_vbv/';

    /**
     * Acao do webservice que realiza a captura da transacao
     */
    const ACAO_CAPTURAR = 'capture.exe';

    /**
     * Acao do webservice que realiza o cancelamento da transacao
     */
    const ACAO_CANCELAR = 'cancel.exe';

    /**
     * Transacao capturada com sucesso
     */
    const CAPTURA_SUCESSO = 0;

    /**
     * Transacao capturada com sucesso
     */
    const CAPTURA_NEGADA = 1;

    /**
     * Transacao excedeu o limite de dias para captura
     */
    const CAPTURA_EXCEDEU_LIMITE_DIAS = 2;

    /**
     * Transacao ja capturada anteriormente
     */
    const CAPTURA_JA_EFETUADA = 3;

    /**
     * Erro no processo de captura
     */
    const CAPTURA_ERRO = 99;


    /**
     * Efetuando a captura de uma transacao
     * Para efetuar a captura e necessario ja ter setado o tid e o merchid
     * ATENCAO: A seguinte variavel RECEIVEXML deve estar setada como 1
     * no arquivo de configuracao (.ini) do cliente no servidor windows
     *
     * @return array('status' => bool, 'msg' => string);
     */
    public function capturarTransacao() {

        if(!$this->getTid())
            throw new Exception('TID não está definido');

        if(!$this->getMerchid())
            throw new Exception('Merchid não está definido');

        if(!$this->getValorTransacao())
            throw new Exception('O valor da transação não foi definido');

        if(!$this->getWebservice())
            throw new Exception('Endereço do webservice não está definido');

        // parametros a seres passados para o webservice para efetuar a captura
        $params = array(
            'tid' => $this->getTid(),
            'merchid' => $this->getMerchid(),
            'price' => $this->getValorTransacao()
         );
        $res = Http::post($this->getWebservice() . self::ACAO_CAPTURAR, $params);

        // caso o retorno nao seja xml, eh uma string com a descricao do erro
        if(strpos($res, 'xml') === false)
            throw new Exception(strip_tags($res));

        $xml = simplexml_load_string($res);

        if(!is_object($xml))
            throw new Exception('Erro na comunicação com o webservice');

        switch(trim($xml->LR)) {

            case self::CAPTURA_SUCESSO:
                $msg    = 'Captura realizada com sucesso.';
                $status = false;
                break;

            case self::CAPTURA_JA_EFETUADA:
                $msg    = 'Captura ja efetuada anteriormente.';
                $status = true;
                break;

            case self::CAPTURA_NEGADA:
                $msg    = 'Captura negada pelo Visanet.';
                $status = false;
                break;

            case self::CAPTURA_ERRO:
                $msg    = 'Ocorreu um erro ao efetuar a captura. (Verifique a existência do TID)';
                $status = false;
                break;
            
            case self::CAPTURA_EXCEDEU_LIMITE_DIAS:
                $msg    = 'Excedeu o limite de dias da autorizacao';
                $status = false;
                break;

            default:
                $msg    = (isset($xml->ARS) && !empty($xml->ARS)) ? $xml->ARS : 'Nao foi possivel identificar o status da captura';
                $status = false;
                break;
            
        }

        return array('status' => $status, 'msg' => $msg);

    }
    

    /**
     * O numero de afilicao do visanet deve possui 10 digitos
     *
     * @param string $afiliacao
     */
    public function setAfiliacao($afiliacao) {
        $afiliacao = trim($afiliacao);
        if(strlen($afiliacao) != 10)
            throw new Exception('Número de Afilicação inválido');
        $this->afiliacao = $afiliacao;
    }


    /**
     * A forma de pagamento do visanet deve possui 10 digitos
     *
     * @param string $formaPagamento
     */
    public function setFormaPagamento($formaPagamento) {
        $formaPagamento = trim($formaPagamento);
        if(strlen($formaPagamento) != 4)
            throw new Exception('Forma de Pagamento inválida');
        $this->formaPagamento = $formaPagamento;
    }


    /**
     * O merchid e o nome do arquivo que foi enviado pela equipe do visanet
     * 
     * @param string $merchid 
     */
    public function setMerchid($merchid) {
        $this->merchid = $merchid;
    }

    public function setTid($tid) {
        if(strlen($tid) != 20)
            throw new Exception('TID Inválido');
        $this->tid = $tid;
    }


    public function setValorTransacao($valor) {
        $this->valorTransacao = number_format($valor, 2, '', '');
    }


    /**
     * @return string codigo da forma de pagamento
     */
    public function getFormaPagamento() {
        return $this->formaPagamento;
    }


    /**
     * @return string codigo da maquineta
     */
    public function getMaquineta() {
        return substr($this->getAfiliacao(), 4, 5);
    }

    /**
     *
     * @return string nome do arquivo de configuracao do cliente
     */
    public function getMerchid() {
        return $this->merchid;
    }


    public function gerarTid() {

        // Número da Maquineta
        $maquineta = $this->getMaquineta();

        // Hora Minuto Segundo e Décimo de Segundo
        $time = date("His").substr(sprintf("%0.1f",microtime()),-1);

        // Obter Data Juliana
        $dataJuliana = sprintf("%03d",(date("z")+1));

        // Último dígito do ano
        $ano = substr(date("y"), 1, 1);

        $tid = $maquineta . $ano . $dataJuliana . $time . $this->getFormaPagamento();
        
        $this->setTid($tid);
        return $this->getTid();

    }
    
}