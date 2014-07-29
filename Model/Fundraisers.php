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
  * Variable that holds an array of other models data that is used in this model.
  * @var array
  */
  public $models = array('Error', 'Countries', 'Activities', 'Events');

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
    $dummy_content = MODEL_ROOT .'data/FundraiserRecords.json';
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
    $extra = null;

    $api_keys = $this->read_file(MODEL_ROOT . 'data/ApiKeys.json', true);

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
              if(!empty($data) && strlen($data) < 3){
                $error = '001.02.010';
                $replace = array(
                  array('{{field}}'),
                  array($key),
                );
              }
              break;

            // Validation for forename.
            case 'forename':
              if(!empty($data) && strlen($data) < 3){
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

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
              $replace = array(
                array('{{field}}'),
                array($key)
              );
          }
          break;

        /**
         * Fundraiser details API validation rules.
         */
        case 'urls/teams':
        case 'urls':
          // Pull in some dummy fake URLs that will be merged with users urls.
          $urls = array();
          foreach($this->data as $record){
            if($record['personalUrl'] && !empty($record['personalUrl'])){
              $urls[] = $record['personalUrl'];
            }
          }

          switch($key){
            // Validation for url.
            case 'url':
              if((!$data || empty($data)) || (strlen($data) > 45 || strlen($data) < 3)){
                $error = '001.00.002';
              } elseif(in_array($data, $urls)){
                $error = '001.00.010';
                for($c = 1; $c < 6; $c++){
                  $extra['messageDetails'][] = $data.$c;
                }
              }
              break;

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
              $replace = array(
                array('{{field}}'),
                array($key)
              );
          }
          break;

        /**
         * Fundraiser API create account validation rules.
         */
        case 'newaccount':
          switch($key){

            // Validation for url.
            case 'countryCode':
              $codes = array();
              foreach($this->Countries->data['countries'] as $country){
                $codes[] = $country['countryCode'];
              }
              if(!in_array($data, $codes)){
                $error = '002.01.03';
              }
              break;

            // Validation for title.
            case 'title':
              $accepted_titles = array('Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof');
              if(!$data || empty($data) || !in_array($data, $accepted_titles)){
                $error = '002.01.04';
              }
              break;

            // Validation for personalUrl
            case 'personalUrl':
              // @todo Loop back and validate URL.
              break;

            // Validation for surname.
            case 'surname':
              if(!$data || empty($data) || strlen($data) > 50){
                $error = '002.01.06';
              }
              break;

            // Validation for forename.
            case 'forename':
              if(!$data || empty($data) || strlen($data) > 50){
                $error = '002.01.07';
              }
              break;

            // Validation for terms and conditions.
            case 'termsAndConditionsAccepted':
              if(!$data || empty($data) || strtoupper($data) != 'Y'){
                $error = '002.01.08';
              }
              break;

            // Validation for email address.
            case 'emailAddress':
              if(!$data || empty($data) || $data > 255){
                $error = '002.01.09';
              }
              // Email regex.  This is in by no means perfect, but will work.
              $email_regx = "~^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}\~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$~";
              if(!$data || empty($data) || !preg_match($email_regx, $data)){
                $error = '002.01.10';
              }
              break;

            // Validation for telephone.
            case 'preferredTelephone':
              // Check to see if the string passed is numeric (without letters).
              if(is_numeric($data)){
                if(!$data || empty($data)){
                  $error = '002.01.11';
                } elseif(strlen($data) > 16){
                  $error = '002.01.12';
                }
              } else {
                $error = '002.01.12';
              }
              break;

            // Validation for date of birth.
            case 'dateOfBirth':
              $dob_regx = "/^[0-9]{4}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/";
              if(!$data || empty($data)){
                $error = '002.01.13';
              } elseif(!preg_match($dob_regx, $data)){
                $error = '002.01.14';
              } else {
                // Check the age is between 13 and 100.
                $from = new DateTime($data);
                $to = new DateTime('today');
                if($from->diff($to)->y < 13 || $from->diff($to)->y > 100){
                  $error = '002.01.15';
                }
              }
              break;

            // Validation for postcode.
            case 'postcode':
              $postcode_regex = "/^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]) ?[0-9][ABD-HJLNP-UW-Z]{2})$/";
              if(strlen($data) > 8){
                $error = '002.01.16';
              } elseif(!preg_match($postcode_regex, strtoupper($data))) {
                $error = '002.01.17';
              }
              break;

            // Validation for addressLine1 / townCity.
            case 'addressLine1/townCity':
              if((!$data['addressLine1'] || empty($data['addressLine1'])) || (!$data['townCity'] || empty($data['townCity']))){
                $error = '002.01.19';
              }
              break;

            // Validation for addressLine1 / townCity.
            case 'customCodes':
              if(!is_array($data) || count($data) < 5){
                $error = '002.01.22';
              } elseif(count($data) > 5){
                $error = '002.01.23';
              }
              break;

            // Validation for emailAddress / dateOfBirth.
            case 'emailAddress/dateOfBirth':
              foreach($this->data as $record){
                // Check that the new account isn't already in the records.
                if(($record['emailAddress'] === $data['emailAddress']) && ($record['dateOfBirth'] === $data['dateOfBirth'])){
                  $error = '002.01.31';
                }
              }
              break;

            // Validation for address fields to make sure they are not empty.
            case 'addressLine1':
            case 'addressLine2':
            case 'townCity':
            case 'countyState':
              if(!$data || empty($data)){
                $error = '001.00.001';
                $replace = array(
                  array('{{field}}'),
                  array($key)
                );
              }
              break;

            // Validation for general Y/N questions.
            case 'charityMarketingIndicator':
            case 'allCharityMarketingIndicator':
            case 'virginMarketingIndicator':
            case 'vmgMarketingIndicator':
              if(!$data || empty($data) || !in_array(strtoupper($data), array('Y', 'N'))){
                $error = '001.00.001';
                $replace = array(
                  array('{{field}}'),
                  array($key)
                );
              }
              break;

            // Validation for resourceID.
            case 'charityResourceId':
              if(!$data || empty($data)){
                $error = '001.01.004';
              }
              break;

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
              $replace = array(
                array('{{field}}'),
                array($key)
              );
          }
          break;

         /**
         * Fundraiser newpage API validation rules.
         */
        case 'account/secure/newpage':
          switch($key){

            // Validation for teamName.
            case 'teamName':
              if(!$data || empty($data)){
                $error = '003.01.01';
              } elseif($data > 255){
                $error = '003.01.02';
              }
              break;

            // Validation for teamUrl.
            case 'teamUrl':
              if($data > 255){
                $error = '003.01.03';
              }
              break;

            // Validation for pageTitle.
            case 'pageTitle':
              if(!$data || empty($data) || strlen($data) > 45){
                $error = '003.01.05';
              }
              break;

            // Validation for fundraisingDate.
            case 'fundraisingDate':
              $date_regx = "/^[0-9]{4}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/";
              if(!$data || empty($data) || !preg_match($dob_regx, $data)){
                $error = '003.01.07';
              } else {
                // Make sure the date is in the future.
                $from = new DateTime($data);
                $to = new DateTime('today');
                if($from->diff($to)->y < 1){
                  $error = '003.01.08';
                }
              }
              break;

            // Validation for teamPageIndidcator.
            case 'teamPageIndicator':
              if(!$data || empty($data) || !in_array(strtoupper($data), array('Y', 'N'))){
                $error = '003.01.09';
              }
              break;

            // Validation for teamPageIndidcator.
            case 'teamUrl':
              if(!$data || empty($data) || $data > 100){
                $error = '003.01.11';
              }
              break;

            // Validation for charityResourceId.
            case 'charityResourceId':
              if(!$data || empty($data)){
                $error = '003.01.12';
              } elseif(is_array($data) && count($data) > 5){
                $error = '003.01.13';
              }
              break;

            // Validation for charitySplits.
            case 'charitySplits':
              if($data || !empty($data)){
                $total = 0;
                foreach($data as $k => $charity){
                  if((!$charity['charityResourceId'] || empty($charity['charityResourceId'])) || (!$charity['charitySplitPercent'] || empty($charity['charitySplitPercent']))){
                    $this->set_error('002.01.30');
                  } else {
                    $total += $charity['charitySplitPercent'];
                  }
                }
                if($total != 100){
                  $error = '003.01.15';
                }
              } else {
                $error = '003.01.15';
              }
              break;

            // Validation for postEventFundraisingInterval.
            case 'postEventFundraisingInterval':
              if(!$data || empty($data) || ($data < 1 || $data > 36)){
                $error = '003.01.16';
              }
              break;

            // Validation for charitycontributionIndicator.
            case 'charitycontributionIndicator':
              if(!$data || empty($data) || !in_array(strtoupper($data), array('Y', 'N'))){
                $error = '003.01.17';
              }
              break;

            // Validation for activityCode.
            case 'activityCode':
              $act_codes = array();
              foreach($this->Activities->data['activityTypes'] as $activity){
                $act_codes[] = $activity['activityCode'];
              }
              if(!$data || empty($data) || !in_array($data, $act_codes)){
                $error = '003.01.18';
              }
              break;

            // Validation for activityDescription.
            case 'activityDescription':
              if(!$data['activityDescription'] || empty($data['activityDescription'])){
                $error = '003.01.19';
              }
              break;

            // Validation for eventResourceId.
            case 'eventResourceId':
              if(!$data['eventResourceId'] || empty($data['eventResourceId'])){
                $error = '002.01.24';
              } else {
                foreach($this->Events->data['events'] as $event){
                  if($data['eventResourceId'] == $event['eventResourceId']){
                    $from = strtotime($event['eventDate']);
                    $to = strtotime('today');
                    if(($from - $to) < 0){
                      $this->set_error('002.01.25');
                    }
                    if($events['charities'] && !empty($events['charities'])){
                      if(count($data['charitySplits']) > count($event['charities'])){
                        $this->set_error('002.01.26');
                      }
                      foreach($event['charities']['charity'] as $key => $charity){
                        $echarities[] = $charity['charityResourceId'];
                      }
                      foreach($data['charitySplits'] as $ckey => $csplit){
                        $scharities[] = $csplit['charityResourceId'];
                      }
                      if(!in_array($scharities, $echarities)){
                        $this->set_error('003.01.20');
                      }
                    }
                    if($event['eventFee']){
                      $this->set_error('002.01.27');
                    }
                  }
                }
              }
              break;

            // When no custom rules, invalid value.
            default:
              $error = '001.00.001';
              $replace = array(
                array('{{field}}'),
                array($key)
              );


          }
          break;
      }
    } else {
      // Setup some validation rules outside of an area, such as the api key.
      switch($key) {
        // Validation to see if api_key is there.
        case 'api_key':
          if(!$data || empty($data) || !in_array($data, $api_keys)) {
            $error = '000.00.003';
          }
          break;
      }
    }

    // If an error code exists.
    if($error){
      return $this->set_error($error, $replace, $extra);
    } else {
      return true;
    }
  }
}
