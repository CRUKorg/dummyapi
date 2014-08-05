<?php
/**
 * Fundraisers controller.
 * This is the controller for the fundraisers route.
 *
 * In this controller, there are multiple switches to determine which
 * end point will be exposed to the users.
 *
 * The controller assumes that it is to return JSON and as such,
 * headers aren't needed to get the data, however it is recommended to build the complete
 * calls as per the documentation.
 *
 * @link https://developer.virginmoneygiving.com/docs
 *
 * A valid API key needs to be passed in a GET parameter.
 *
 * Paths for the fundraisers end point are built up as follows:
 * @example http://host/fundraisers/v1/xxx(/xxx)?api_key=xxxxxxxxxxxxx
 *
 * @package Framework
 * @category Controller
 * @author Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class FundraisersController extends Controller{
  /**
   * Implements init();
   * Initialise the controller.
   *
   * There are multiple switches in this method that will determine what data
   * to return to the user.
   *
   * @uses Controller::model() to set the data model.
   * @uses FundraisersController::output_json() function to return formatted JSON.
   * @uses FundraisersModel::validate() to validate the data being parsed.
   * @uses FundraisersModel::set_error() to set the correct error message.
   *
   * @return void
   */
  public function init(){
    $this->model('Fundraisers');

    // Get all the request headers for this request.
    $request_headers = getallheaders();

    // Setup an empty array to output.
    $return_data = array();
    $expected_return = array();

    // Remove all the .json from actions.
    foreach($this->router->action as $rk => $rv){
      $this->router->action[$rk] = str_replace(array('.json', '.xml'), '', $rv);
    }


    // Check to see if there is a version number.
    if(isset($this->router->action[1])){
      // Switch to determine which version to pull through.
      switch ($this->router->action[1]){

        /**
         * Switch case for API version 1.
         */
        case 'v1' :
          if(isset($this->router->action[2])){
            // Check if the API key is there.
            if($_GET && !empty($_GET['api_key'])){
              $this->model->validate('api_key', $_GET['api_key']);
            } else {
              $this->model->Error->set_error('000.00.003');
            }
            if(!empty($this->model->Error->errors)){
              $this->output_json($this->model->Error->errors);
              // Kill the app before it does anything else.
              die();
            } else {
              // Remove the API key from the get request since we have validated it.
              unset($_GET['api_key']);
            }

            // Tell the model what rules to look at.
            $this->model->area = $this->router->action[2];

            /**
             * Switch case for Fundraiser Action (v1).
             */
            switch ($this->model->area){

              /**
               * @api
               * Case holds logic for endpoint: fundraisers/v1/search
               */
              case 'search' :
                // Setup some expected parameters.
                $expected_array = array(
                  'surname' => array('required' => true),
                  'forename' => array('required' => true)
                );
                // Say what fields we are expecitng to return.
                $expected_return = array('fundraiserName', 'title', 'forename', 'surname', 'resourceId', 'personalUrl, fundraisingURI');

                // Check if there are any GET parameters to deal with.
                if($_GET){
                  // Validate if we have the criteria.
                  $this->model->validate('criteria', $_GET);
                  // Loop through all the $_GET parameters and check there are no invalid values.
                  foreach($_GET as $getk => $getv){
                    // Check for keys that aren't expected.
                    if(!in_array($getk, array_keys($expected_array))){
                      // Run it through the validate to get the error message.
                      $this->model->validate($getk, $getv);
                    }
                  }
                  foreach($expected_array as $expk => $expv) {
                    // Only validate if the expected array element is required.
                    if($expv['required']) {
                      $this->model->validate($expk, $_GET[$expk]);
                    }
                  }
                }

                if(empty($this->model->Error->errors)){
                  foreach($this->model->data as $record){
                    $search = array(strtoupper($_GET['forename']), strtoupper($_GET['surname']));
                    $compare = array(strtoupper($record['forename']), strtoupper($record['surname']));

                    if(in_array($search, $compare) ||
                      (strtoupper($record['surname']) == strtoupper($_GET['surname']) && stristr($record['forename'], $_GET['forename'])) ||
                      (strtoupper($record['forename']) == strtoupper($_GET['forename']) && stristr($record['surname'], $_GET['surname']))){

                      // Return the expected fields from the record.
                      foreach($expected_return as $field){
                        $return_data[$field] = $record[$field];
                      }
                      // Break out the loop.
                      break;
                    } else {
                      $this->model->Error->set_error('001.02.011');
                      $return_data = $this->model->Error->errors;
                    }
                  }
                } else {
                  $return_data = $this->model->Error->errors;
                }
                $this->output_json($return_data);
                break;

              /**
               * @api
               * Case holds logic for endpoint: fundraisers/v1/account
               */
              case 'account' :
                /**
                 * Secure account stuff happens here.
                 */
                if(isset($this->router->action[3]) && $this->router->action[3] == 'secure'){
                  $fundraiser = $this->router->action[4];
                  $this->model->area .= "/{$this->router->action[3]}/{$this->router->action[5]}";

                  /**
                   * Switch for secure elements of the account.
                   */
                  switch($this->router->action[5]){

                    /**
                     * @api
                     * Case holds logic for endpoint: fundraisers/v1/account/secure/{fundraiserResourceId}/newpage
                     */
                    case 'newpage':
                      $expected_array = array(
                        'pageTitle' => array('required' => true),
                        'eventResourceId' => array('required' => false),
                        'fundraisingDate' => array('required' => false),
                        'teamPageIndicator' => array('required' => true),
                        'teamName' => array('required' => false),
                        'teamUrl' => array('required' => false),
                        'activityCode' => array('required' => false),
                        'activityDescription' => array('required' => true),
                        'charitycontributionIndicator' => array('required' => true),
                        'postEventFundraisingInterval' => array('required' => true),
                        'fundraisingTarget' => array('required' => false),
                        'charitySplits' => array('required' => true),
                      );

                      $request_body = file_get_contents('php://input');

                      // Check if the request is JSON, otherwise it's a postbody.
                      if(json_decode($request_body, true)){
                        $data = json_decode($request_body, true);
                      } else {
                        parse_str($request_body, $data);
                      }

                      if($data){
                        if(empty($data)){
                          $this->model->Error->set_error('003.01.04');
                        }
                         // Loop through all the $_POST parameters and check there are no invalid values.
                        foreach($data as $getk => $getv){
                          // Check for keys that aren't expected.
                          if(!in_array($getk, array_keys($expected_array))){
                            // Run it through the validate to get the error message.
                            $this->model->validate($getk, $getv);
                          }
                        }
                        $this->model->validate('pageTitle', $data['pageTitle']);
                        if($this->model->validate('teamPageIndicator', $data['teamPageIndicator'])){
                          if($data['teamPageIndicator'] == 'Y'){
                            $this->model->validate('teamName', $data['teamName']);
                            $this->model->validate('teamUrl', $data['teamUrl']);
                          }
                        }
                        if(!$data['eventResourceId'] || empty($data['eventResourceId'])){
                          $this->model->validate('activityCode', $data['activityCode']);
                        } else {
                          $this->model->validate('eventResourceId', array('eventResourceId' => $data['eventResourceId'], 'charitySplits' => $data['charitySplits']));
                        }
                        if(($data['activityCode'] || !empty($data['activityCode'])) && $data['activityCode'] == '039'){
                          $this->model->validate('activityDescription', $data['activityDescription']);
                        }

                        $this->model->validate('charitycontributionIndicator', $data['charitycontributionIndicator']);
                        $this->model->validate('postEventFundraisingInterval', $data['postEventFundraisingInterval']);

                        $this->model->validate('charityResourceId', $data['charitySplits']);
                        $this->model->validate('charitySplits', $data['charitySplits']);
                      } else{
                        $this->model->Error->set_error('003.01.04');
                      }

                      // If we haven't errored out yet, this is the last thing to do.
                      if(empty($this->model->Error->errors)){
                        $return_data = array(
                          'creationSuccessful' => true,
                          'pageTitle' => $data['pageTitle'],
                          'pageUrl' => 1,
                          'pageURI' => "http://{$_SERVER['HTTP_HOST']}/fundraisers/v1/account/{$this->router->action[4]}/page/1"
                        );
                      } else {
                        $return_data = $this->model->Error->errors;
                      }

                      $this->output_json($return_data);
                      break;

                  }
                }

                /**
                 * @api
                 * Case holds logic for endpoint: fundraisers/v1/account
                 */
                else {
                  // Say what fields we are expecitng to return.
                  $expected_return = array('fundraiserName', 'title', 'forename', 'surname', 'resourceId', 'personalUrl', 'fundraiserURI', 'pageSummary');

                  // If we haven't errored out yet, this is the last thing to do.
                  if(empty($this->model->Error->errors)){
                    foreach($this->model->data as $record){
                      if($this->router->action[3] == $record['resourceId']){
                        // Return the expected fields from the record.
                        foreach($expected_return as $field){
                          $return_data[$field] = $record[$field];
                        }
                        // Break out the loop.
                        break;
                      } else {
                        $this->model->Error->set_error('001.02.011');
                        $return_data = $this->model->Error->errors;
                      }
                    }
                  } else {
                    $return_data = $this->model->Error->errors;
                  }
                  $this->output_json($return_data);
                }
                break;

              /**
               * @api
               * Case holds logic for endpoints:
               * - fundraisers/v1/urls
               * - fundraisers/v1/urls/teams
               */
              case 'urls' :
                // Check if there is a 'teams';
                if($this->router->action[3]){
                  // If it's teams, we need to shift the action to 4.
                  if($this->router->action[3] == 'teams'){
                    // Concatanate the teams for the model.
                    $this->model->area .= "/teams";
                    $c = 4;
                  } else {
                    $c = 3;
                  }
                  $url_to_validate = $this->router->action[$c];
                } else {
                  $this->model->Error->set_error('001.00.002');
                }

                // Check the url passed through.
                $this->model->validate('url', $url_to_validate);

                // If we haven't errored out yet, this is the last thing to do.
                if(empty($this->model->Error->errors)){
                  $return_data = array(
                    'requestedUrl' => $url_to_validate,
                    'urlType' => "fundraiser",
                    'available' => true,
                    'message' => null,
                    'alternateUrlList' => array()
                  );
                } else {
                  $return_data = $this->model->Error->errors;
                }
                $this->output_json($return_data);
                break;

              /**
               * Case holds logic for endpoints: fundraisers/v1/newaccount.json
               */
              case 'newaccount' :
                // Setup some expected parameters.
                $expected_array = array(
                  'title' => array('required' => true),
                  'forename' => array('required' => true),
                  'surname' => array('required' => true),
                  'addressLine1' => array('required' => true),
                  'addressLine2' => array('required' => true),
                  'townCity' => array('required' => true),
                  'countyState' => array('required' => true),
                  'postcode' => array('required' => true),
                  'countryCode' => array('required' => true),
                  'preferredTelephone' => array('required' => true),
                  'emailAddress' => array('required' => true),
                  'personalUrl' => array('required' => true),
                  'termsAndConditionsAccepted' => array('required' => true),
                  'charityMarketingIndicator' => array('required' => true),
                  'allCharityMarketingIndicator' => array('required' => true),
                  'virginMarketingIndicator' => array('required' => true),
                  'vmgMarketingIndicator' => array('required' => true),
                  'dateOfBirth' => array('required' => true),
                  'fundraiserCustomCode' => array('required' => false),
                  'charityResourceId' => array('required' => false),
                );

                $request_body = file_get_contents('php://input');

                // Check if the request is JSON, otherwise it's a postbody.
                if(json_decode($request_body, true)){
                  $data = json_decode($request_body, true);
                } else {
                  parse_str($request_body, $data);
                }

                // Setup an empty array to output.
                $return_data = array();
                $expected_return = array();

                foreach($this->router->action as $rk => $rv){
                  $this->router->action[$rk] = str_replace('.json', '', $rv);
                }

                if($data){
                  if(!$data || empty($data)){
                    $this->model->Error->set_error('002.01.02');
                  }
                   // Loop through all the $_GET parameters and check there are no invalid values.
                  foreach($data as $getk => $getv){
                    // Check for keys that aren't expected.
                    if(!in_array($getk, array_keys($expected_array))){
                      // Run it through the validate to get the error message.
                      $this->model->validate($getk, $getv);
                    }
                  }
                  foreach($expected_array as $expk => $expv) {
                    // Only validate if the expected array element is required.
                    if($expv['required']) {
                      $this->model->validate($expk, $data[$expk]);
                    }
                  }
                  $this->model->validate('addressLine1/townCity', array('addressLine1' => $data['addressLine1'], 'townCity' => $data['townCity']));
                  $this->model->validate('emailAddress/dateOfBirth', array('emailAddress' => $data['emailAddress'], 'dateOfBirth' => $data['dateOfBirth']));
                  if(isset($data['customCodes'])){
                    $this->model->validate('customCodes', $data['fundraiserCustomCode']);
                  }
                  if(isset($data['charityResourceId'])){
                    $this->model->validate('charityResourceId', $data['charityResourceId']);
                  }
                } else {
                  $this->model->Error->set_error('002.01.01');
                }

                // If we haven't errored out yet, this is the last thing to do.
                if(empty($this->model->Error->errors)){
                  $return_data = array(
                    'creationSuccessful' => true,
                    'fundraiserName' => "{$data['title']} {$data['forename']} {$data['surname']}",
                    'resourceId' => "318fc319-3b19-4314-9c8d-404b14e51eb2",
                    'personalUrl' => "http://uk.sandbox.virginmoneygiving.com/mytesturl",
                    'alternateURLs' => null,
                    'accessToken' => "mdznarvxftjk2p365tyv7zp8",
                    'responseURI' => "https://sandbox.api.virginmoneygiving.com/fundraisers/v1/account/318fc319-3b19-4314-9c8d-404b14e51eb2",
                    'customerExists' => false,
                    'message' => "Token expires in 600 seconds"
                  );
                } else {
                  $return_data = $this->model->Error->errors;
                }
                $this->output_json($return_data);
                break;

            }
          } else Router::redirect('/404');
          break;
      }
    } else Router::redirect('/404');
  }

  /**
   * Implements output_json();
   *
   * Function to simplify the ouput of the json to the page.
   */
  private function output_json($data){
    echo json_encode($data);
  }
}
