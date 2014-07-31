<?php
/**
 * Error Codes Model.
 *
 * Model to hold error information, for use elsewhere.
 *
 * @package:  Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class ErrorModel extends Model{
  /**
  * This variable holds all errors that happen in the app.  It is used mainly for api endpoints.
  * @var array
  */
  public $errors;

  /**
   * Implements init();
   *
   * Initialise the Error Model.
   *
   * @return void
   */
  public function init(){
    $error_location = MODEL_ROOT . 'data/ErrorCodes.json';
    $this->read_file($error_location);
  }

  /**
   * Implements set_error();
   *
   * Function that will set error message to output.
   *
   * @param string  $code     This is the actual error code being set.
   * @param array   $replace  A multidimensional array that holds replacement tokens and corresponding data.
   * @param array   $extra    Add additional array elements to the error message.  Useful for adding messages.
   * 
   * @return boolean
   */
  public function set_error($code, $replace = null, $extra = null){
    if($replace){
      // Replace the tokens with the correct data.
      $message = str_replace($replace[0], $replace[1], $this->data[$code]['Explanation']);
    } else {
      $message = $this->data[$code]['Explanation'];
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
