<?php
/**
 * Fundraisers end point controller.
 * The controller to route to the end points for fundraisers.
 *
 * @package: Framework
 * @category: Controller
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class FundraisersController extends Controller{
  /**
   * Implements init();
   *
   * The initialisation of this module..
   */
  public function init(){
    // Get all the request headers for this request.
    $request_headers = getallheaders();
    $this->model('Fundraisers');

    // Setup an empty array to output.
    $return_data = array();
    $expected_return = array();

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
              $this->model->set_error('000.00.000');
            }
            if(!empty($this->model->errors)){
              $this->output_json($this->model->errors);
              die();
            } else {
              unset($_GET['api_key']);
            }

            // Remove the .json (we don't need it in teh dummy API).
            $this->model->area = str_replace('.json', '', $this->router->action[2]);

            /**
             * Switch case for Fundraiser Action (v1).
             */
            switch ($this->model->area){

              /**
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

                if(empty($this->model->errors)){
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
                      $this->model->set_error('001.02.011');
                      $return_data = $this->model->errors;
                    }
                  }
                } else {
                  $return_data = $this->model->errors;
                }
                $this->output_json($return_data);
                break;

              /**
               * Case holds logic for endpoint: fundraisers/v1/account
               */
              case 'account' :
                // Setup some expected parameters.
                $expected_array = array(
                  'resourceId' => array('required' => true)
                );
                // Say what fields we are expecitng to return.
                $expected_return = array('fundraiserName', 'title', 'forename', 'surname', 'resourceId', 'personalUrl, fundraisingURI', 'pageSummary');

                // Check if there are any GET parameters to deal with.
                if($_GET){
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

                // If we haven't errored out yet, this is the last thing to do.
                if(empty($this->model->errors)){
                  foreach($this->model->data as $record){
                    if($record['resourceId'] == $_GET['resourceId']){
                      // Return the expected fields from the record.
                      foreach($expected_return as $field){
                        $return_data[$field] = $record[$field];
                      }
                      // Break out the loop.
                      break;
                    } else {
                      $this->model->set_error('001.02.011');
                      $return_data = $this->model->errors;
                    }
                  }
                } else {
                  $return_data = $this->model->errors;
                }
                $this->output_json($return_data);
                break;

              /**
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
                  $url_to_validate = str_replace('.json', '', $this->router->action[$c]);
                } else {
                  $this->model->set_error('001.00.002');
                }

                // Check the url passed through.
                $this->model->validate('url', $url_to_validate);

                // If we haven't errored out yet, this is the last thing to do.
                if(empty($this->model->errors)){
                  $return_data = array(
                    'requestedUrl' => $url_to_validate,
                    'urlType' => "fundraiser",
                    'available' => true,
                    'message' => null,
                    'alternateUrlList' => array()
                  );
                } else {
                  $return_data = $this->model->errors;
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

                if($_POST){
                  if(empty($_POST)){
                    $this->model->set_error('002.01.02');
                  }
                   // Loop through all the $_GET parameters and check there are no invalid values.
                  foreach($_POST as $getk => $getv){
                    // Check for keys that aren't expected.
                    if(!in_array($getk, array_keys($expected_array))){
                      // Run it through the validate to get the error message.
                      $this->model->validate($getk, $getv);
                    }
                  }
                  foreach($expected_array as $expk => $expv) {
                    // Only validate if the expected array element is required.
                    if($expv['required']) {
                      $this->model->validate($expk, $_POST[$expk]);
                    }
                  }
                  $this->model->validate('addressLine1/townCity', array('addressLine1' => $_POST['addressLine1'], 'townCity' => $_POST['townCity']));
                  $this->model->validate('emailAddress/dateOfBirth', array('emailAddress' => $_POST['emailAddress'], 'dateOfBirth' => $_POST['dateOfBirth']));
                  if(isset($_POST['customCodes'])){
                    $this->model->validate('customCodes', $_POST['fundraiserCustomCode']);
                  }
                  if(isset($_POST['charityResourceId'])){
                    $this->model->validate('charityResourceId', $_POST['charityResourceId']);
                  }
                } else {
                  $this->model->set_error('002.01.01');
                }

                // If we haven't errored out yet, this is the last thing to do.
                if(empty($this->model->errors)){
                  $return_data = array(
                    'title' => $_POST['title'],
                    'forename' => $_POST['forename'],
                    'addressLine1' => $_POST['addressLine1'],
                    'addressLine2' => $_POST['addressLine2'],
                    'townCity' => $_POST['townCity'],
                    'countyState' => $_POST['countyState'],
                    'postcode' => $_POST['postcode'],
                    'countryCode' => $_POST['countryCode'],
                    'preferredTelephone' => $_POST['preferredTelephone'],
                    'emailAddress' => $_POST['emailAddress'],
                    'personalUrl' => $_POST['personalUrl'],
                    'termsAndConditionsAccepted' => $_POST['termsAndConditionsAccepted'],
                    'charityMarketingIndicator' => $_POST['charityMarketingIndicator'],
                    'allCharityMarketingIndicator' => $_POST['allCharityMarketingIndicator'],
                    'virginMarketingIndicator' => $_POST['virginMarketingIndicator'],
                    'dateOfBirth' => $_POST['dateOfBirth'],
                    'vmgMarketingIndicator' => $_POST['vmgMarketingIndicator'],
                  );
                } else {
                  $return_data = $this->model->errors;
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
