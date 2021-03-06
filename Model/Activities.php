<?php
/**
 * Activities Model.
 *
 * This model simulates the activities API by allowing access to
 * the data stored in the json files when invoked.
 *
 * @package: Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class ActivitiesModel extends Model{

  /**
   * Implements init();
   *
   * Initialise the Activites model and make the data available.
   *
   * @return void
   */
  public function init(){
    // Pull in some dummy records into the data.
    $dummy_content = MODEL_ROOT . 'data/ActivityCodes.json';
    $this->read_file($dummy_content);
  }
}
