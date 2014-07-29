<?php
/**
 * Overall Model
 * The parent model for the app.
 *
 * @package: Framework
 * @category: Model
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

  /**
   * Implements read_file();
   *
   * Function that will read dummy content.
   */
  public function read_file($path, $return = false){
    if(file_exists($path)){
      $file = file_get_contents($path);
      if($return) {
        return json_decode($file, true);
      } else {
        $this->data = json_decode($file, true);
      }
    } else return 'Dummy Records not found!';
  }

  /**
   * Implements set_error();
   *
   * Function that will set error message to output.
   */
  public function set_error($code, $replace = null, $extra = null){
    if($replace){
      // Replace the tokens with the correct data.
      $message = str_replace($replace[0], $replace[1], $this->Error->errors[$code]['Explanation']);
    } else {
      $message = $this->Error->errors[$code]['Explanation'];
    }
    // Set the error code and messages as determined above.
    $this->errors['errors'][] = array(
      'errorCode' => $code,
      'errorMessage' => $message
    );

    // Add extra stuff to the error message.
    if($extra){
      foreach($extra as $exk => $exv){
        $this->errors['errors'][count($this->errors['errors'])-1][$exk] = $exv;
      }
    }

    // Remove any duplicates from the array for cleaner output.
    $this->errors['errors'] = array_map('unserialize', array_unique(array_map('serialize', $this->errors['errors'])));
    return false;
  }
}
