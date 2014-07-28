<?php
/**
 * Fundraisers end point model.
 * Model holds the data (dummy data) and
 * validation rules for fundraisers end point.
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
  public $models = array('Error', 'Countries');

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
    $this->read_file($dummy_content);
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
    $replace = null;

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
                $error = '000.00.000';
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
        case 'urls/teams':
        case 'urls':
          // Change the dummy content being pulled in.
          $this->read_file(DIR_ROOT . "/Config/DummyUrlsTaken.json");
          switch($key){

            // Validation for url.
            case 'url':
              if((!$data || empty($data)) || (strlen($data) > 45 || strlen($data) < 3)){
                $error = '001.00.002';
              }
              break;

            // Validation to see if api_key is there.
            case 'api_key':
              if(!$data || empty($data)) {
                $error = '000.00.000';
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
      return $this->set_error($error, $replace);
    } else {
      return true;
    }
  }
}
