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
    $this->model('Fundraisers');
    // Check to see if there is a version number.
    if(isset($this->router->action[1])){
      // Switch to determine which version to pull through.
      switch ($this->router->action[1]){

        /**
         * Switch case for API version 1.
         */
        case 'v1' :
          if(isset($this->router->action[2])){
            $this->model->area = $this->router->action[2];

            /**
             * Switch case for Fundraiser Action (v1).
             */
            switch ($this->router->action[2]){

              /**
               * Case holds logic for endpoint: fundraisers/v1/search
               */
              case 'search' :
                // Setup some expected parameters.
                $expected_array = array(
                  'surname' => array(
                    'required' => true,
                  ),
                  'forename' => array(
                    'required' => true,
                  ),
                  'api_key' => array(
                    'required' => true,
                  )
                );
                // Say what fields we are expecitng to return.
                $expected_return = array('fundraiserName', 'title', 'forename', 'surname', 'resourceId', 'personalUrl, fundraisingURI');

                // Setup an empty array to output.
                $return_data = array();

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
                    } elseif($expected_array[$getk]['required']){
                      // Only validate if the expected array element is required.
                      $this->model->validate($getk, $getv);
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
                      $this->model->setError('001.02.011');
                      $return_data = $this->model->errors;
                    }
                  }
                } else {
                  $return_data = $this->model->errors;
                }
                $this->outputJson($return_data);
                break;

              /**
               * Case holds logic for endpoint: fundraisers/v1/account
               */
              case 'account' :
                // Setup some expected parameters.
                $expected_array = array(
                  'resourceId' => array(
                    'required' => true,
                  ),
                  'api_key' => array(
                    'required' => true,
                  )
                );
                // Say what fields we are expecitng to return.
                $expected_return = array('fundraiserName', 'title', 'forename', 'surname', 'resourceId', 'personalUrl, fundraisingURI', 'pageSummary');
                // Setup an empty array to output.
                $return_data = array();

                // Check if there are any GET parameters to deal with.
                if($_GET){
                  // Loop through all the $_GET parameters and check there are no invalid values.
                  foreach($_GET as $getk => $getv){
                    // Check for keys that aren't expected.
                    if(!in_array($getk, array_keys($expected_array))){
                      // Run it through the validate to get the error message.
                      $this->model->validate($getk, $getv);
                    } elseif($expected_array[$getk]['required']){
                      // Only validate if the expected array element is required.
                      $this->model->validate($getk, $getv);
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
                      $this->model->setError('001.02.011');
                      $return_data = $this->model->errors;
                    }
                  }
                } else {
                  $return_data = $this->model->errors;
                }
                $this->outputJson($return_data);
                break;

            }
          } else Router::redirect('/404');
          break;
      }
    } else Router::redirect('/404');
  }

  /**
   * Implements outputJson();
   *
   * Function to simplify the ouput of the json to the page.
   */
  private function outputJson($data){
    echo json_encode($data);
  }
}
