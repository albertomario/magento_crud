<?php 

class MagentoCrud_SoapClient {
	
	private $soapUri;
	private $soapToken;
	private $soapOpt = [];


	public function __construct($soapUri)
	{
		$this->soapUri = $soapUri;
		$this->soapOpt = ['soap_version' => SOAP_1_2,
						  'trace' => 1,
						  'connection_timeout' => 120];

		//Apel initial pentru colectarea token-ului Bearer Auth
		$soapClient = new SoapClient($this->formatUri(['services' => 'integrationAdminTokenServiceV1']), $this->soapOpt);

		$soapResponse = $soapClient->integrationAdminTokenServiceV1CreateAdminAccessToken([
		    'username' => 'administrator',
		    'password' => 'passadmin!!2'
		]);
		$this->soapToken = $soapResponse->result;


		//TODO Persistenta token in session
		$this->soapOpt['stream_context'] = stream_context_create([
					    'http' => [
					        'header' => sprintf('Authorization: Bearer %s', $this->soapToken)
					    ]
					]);

	}

	public function getList($method)
	{

		$soapClient = new SoapClient($this->formatUri(['services' => $method]), $this->soapOpt);
		return $soapClient->__getFunctions();
	}


	public function __call($method, $args)
	{

		$soapClient = new SoapClient($this->formatUri(['services' => $method]), $this->soapOpt);
		$objSoapArgs = new \stdClass;

		foreach($args[1] as $arg => $value) {
			$objSoapArgs->{$arg} = $value;
		}

		return $soapClient->{$method.$args[0]}($objSoapArgs); // 'Lipim' numele metodei si o apelam
	}


	private function formatUri($newQuery = [])
	{
		$toAppend = ((new \http\QueryString($newQuery))->toString());
		return ((new \http\Url($this->soapUri, ['query' => $toAppend], 2))->toString());
	}
}

 ?>