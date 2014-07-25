<?php
/**
 * Overall Model
 * The parent model for the app.
 *
 * @package: Framework\Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class Model{
  /**
  * Variable that will hold other models to be included in the current model.
  * @var array
  */
	public $models = array();

  /**
   * Implements debug();
   *
   * Custom debug function that will format the output a bit nicer.
   */
	public function debug($data){
    $config = new Config();
    if($config->debug == 1){
		  echo '<pre>';
		  var_dump($data);
		  echo '</pre>';
    }
	}

  /**
   * Implements __construct();
   *
   * Constructer for the model.
   */
	public function __construct(){
		$this->other_models();
		$this->init();
	}

  /**
   * Implements init();
   *
   * Empty init function that is needed.
   */
	public function init(){ }

  /**
   * Implements other_models();
   *
   * Function that will include other models into the current model.
   */
	private function other_models(){
		if(!empty($this->models)){
			foreach($this->models as $model){
				include_once(MODEL_ROOT."{$model}.php");
				$m = $model.'Model';
				$this->$model = new $m;
			}
		}
	}
}
