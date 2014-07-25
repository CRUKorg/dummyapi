<?php
/**
 * Error Codes Model.
 * Model gets the error codes.
 *
 * @package:  Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class ErrorModel extends Model{
  public $errors;

  /**
   * Implements init();
   *
   * Initialise the Error Model.
   */
  public function init(){
    $this->errors = $this->getErrorTypes();
  }

  /**
   * Implements getErrorTypes();
   *
   * Custom function to check for and read from the ErrorCodes json file.
   */
  public function getErrorTypes(){
    $error_location = DIR_ROOT . '/Config/ErrorCodes.json';
    if(file_exists($error_location)){
      $file = file_get_contents($error_location);
      $errors = json_decode($file, true);
      return $errors;
    } else return 'Error Codes not found!';
  }
}
