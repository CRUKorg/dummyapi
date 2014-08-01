<?php
/**
 * Countries Model.
 *
 * This model simulates the country code API by allowing access to
 * the data stored in the json files when invoked.
 * 
 * @package: Framework
 * @category: Model
 * @author: Kris Pomphrey <kris@krispomphrey.co.uk>
 */
class CountriesModel extends Model{

  /**
   * Implements init();
   *
   * Initialise the Countries Model and make the data avilable to the app.
   *
   * @return void
   */
  public function init(){
    // Pull in some dummy records into the data.
    $dummy_content = MODEL_ROOT . 'data/CountryCodes.json';
    $this->read_file($dummy_content);
  }
}
