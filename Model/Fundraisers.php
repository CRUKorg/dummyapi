<?php
/**
 * Fundraisers end point model.
 * Model holds the validation rules for fundraisers end point.
 *
 * @package: Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class FundraisersModel extends Model{
  /**
  * Variable that will hold the DB connection.
  * @var array
  */
  public $models = array('Error');

  /**
  * Variable that holds the api area being called.
  * @var string
  */
  public $area;

  /**
  * Variable that will hold all errors returned by the model.
  * @var array
  */
  public $errors = array();

  /**
   * Implements init();
   *
   * Initialise the Fundraisers Model.
   */
  public function init(){
    // Pull in some dummy records into the data.
    $dummy_content = DIR_ROOT . '/Config/DummyFundraiserRecords.json';
    if(file_exists($dummy_content)){
      $file = file_get_contents($dummy_content);
      $this->data = json_decode($file, true);
    } else return 'Dummy Records not found!';
  }

  /**
   * Implements validate();
   *
   * Custom validation function that checks data integrity, pulling through
   * the correct error message if applicable.
   */
  public function validate($key, $data){
    // Set up variable to hold error code.
    $error = null;

    // Make sure the controller has set what are we are in.  This should be automatic.
    if($this->area){

      /**
       * Switch the rules depending on the API call.
       */
      switch($this->area){

        /**
         * Fundraiser search API validation rules.
         */
        case 'search':
          switch($key){

            // Validation for surname.
            case 'surname':
              if(strlen($data) < 3 && !empty($data)){
                $error = '001.02.010';
                $replace = array(
                  array('{{field}}'),
                  array($key),
                );
              }
              break;

            // Validation for forename.
            case 'forename':
              if(strlen($data) < 3 && !empty($data)){
                $error = '001.02.010';
                $replace = array(
                  array('{{field}}'),
                  array($key),
                );
              }
              break;

            // Validation to see if any search criteria is present.
            case 'criteria':
              if((!$data['surname'] && !$data['forename']) || (empty($data['surname']) && empty($data['forename']))) {
                $error = '001.02.012';
              }
              break;

            // Validation to see if api_key is there.
            case 'api_key':
              if(!$data || empty($data)) {
                $error = '001.00.004';
              }
              break;

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
          }
          break;

          /**
         * Fundraiser details API validation rules.
         */
        case 'account':
          switch($key){

            // Validation for surname.
            case 'resouceId':
              if(!$data || empty($data)){
                $error = '001.02.009';
              }
              break;

            // Validation to see if api_key is there.
            case 'api_key':
              if(!$data || empty($data)) {
                $error = '001.00.004';
              }
              break;

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
          }
          break;
      }
    }

    // If an error code exists.
    if($error){
      return $this->setError($error, $replace);
    } else {
      return true;
    }
  }

  public function setError($code, $replace = null){
    if($replace){
      // Replace the tokens with the correct data.
      $message = str_replace($replace[0], $replace[1], $this->Error->errors[$code]['Explanation']);
    } else {
      $message = $this->Error->errors[$code]['Explanation'];
    }
    // Set the error code and messages as determined above.
    $this->errors[] = array(
      'errorCode' => $code,
      'errorMessage' => $message
    );
    return false;
  }
}
