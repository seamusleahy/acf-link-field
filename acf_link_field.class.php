<?php
/**
 * A link field for Advance Custom Fields plugin
 */
class ACF_Link_Field extends acf_Field
{
  static $targets = array(
    '_self' => 'Default',
    '_blank' => 'Opens in new window or tab',
    '_top' => 'Opens in the full body of the window',
    '_parent' => 'Opens in the parent frame',
  );

	function __construct($parent)	{
		// do not delete!
    	parent::__construct($parent);
    	
    	// set name / title
    	$this->name = 'link_field'; // variable name (no spaces / special characters / etc)
      $this->title = __("Link",'acf'); // field label (Displayed in edit screens)
		
   	}

	
	/**
   * Create field option form
   *
   * Outputs the form fields for the options for the field.
   */
	function create_options($key, $field) {
		// defaults
    $defaults = array(
      'show_target' => false,
      'default_target' => '_self',
      'show_title' => false,
      'classes' => array(),
    );
		
    $field = array_merge($defaults, $field);

		?>    
    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('Show target'); ?></label>
        <p class="description"><?php _e('Allow the user to select if the link opens in a new window, etc.'); ?></p>
      </td>
      <td>
        <input type="checkbox" name="fields[<?php echo $key; ?>][show_target]" value="1" <?php if($field['show_target']) echo 'checked="checked"'; ?> />
      </td>
    </tr>

    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('Default target'); ?></label>
      </td>
      <td>
        <select name="fields[<?php echo $key; ?>][default_target]">
          <?php foreach( ACF_Link_Field::$targets as $k => $l ): ?>
            <option value="<?php echo $k; ?>" <?php if($k == $field['default_target']) echo 'selected="selected"'; ?>><?php _e($l); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>

    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('Show title attribute'); ?></label>
        <p class="description"><?php _e('Allow the user to provided text for the title attribute.'); ?></p>
      </td>
      <td>
        <input type="checkbox" name="fields[<?php echo $key; ?>][show_title]" value="1" <?php if($field['show_title']) echo 'checked="checked"'; ?> />
      </td>
    </tr>

    <tr class="field_option field_option_<?php echo $this->name; ?>">
      <td class="label">
        <label><?php _e('Styles'); ?></label>
        <p class="description"><?php _e('Provided classes for the user to select a style.<br/><br/>btn-big : Big button'); ?></p>
      </td>
      <td>
        <textarea name="fields[<?php echo $key; ?>][classes]" ><?php 
        foreach( $field['classes'] as $class => $label ) {
          echo "$class : $label\n";
        }
        ?></textarea>
      </td>
    </tr>

		<?php
	}
		
	
	
  /**
   * Output the post edit form for the field.
   */
	function create_field($field)	{
		// vars
    $field['value'] = (array) $field['value'];

		$target = isset($field['value']['target']) ? $field['value']['target'] : ( isset($field['default_target']) ? $field['default_target'] : '_self');
    $class = isset($field['value']['class']) ? $field['value']['class'] : '';
    
		// html
    ?>
    <div class="acf-link-field-primary-row">
      <label class="inline"><?php _e('Text'); ?>:</label>
      <input type="text" name="<?php echo $field['name']; ?>[text]" value="<?php echo $field['value']['text']; ?>" />

      <label class="inline"><?php _e('URL'); ?>:</label>
      <input type="text" name="<?php echo $field['name']; ?>[url]" value="<?php echo $field['value']['url']; ?>" />
    </div>

    <?php if( $field['show_title'] || $field['show_target'] || count($field['classes']) ): ?>
    <div class="acf-link-field-secondary-row">
      <?php if( $field['show_title']): ?>
        <label class="inline">Title Attribute</label>
        <input type="text" name="<?php echo $field['name']; ?>[title]"  value="<?php echo $field['value']['title']; ?>" />
      <?php endif; ?>

      <?php if( $field['show_target']): ?>
        <label class="inline"><?php _e('Target'); ?></label>
        <select name="<?php echo $field['name']; ?>[target]">
          <?php foreach( ACF_Link_Field::$targets as $k => $l ): ?>
            <option value="<?php echo $k; ?>" <?php if($k == $target) echo 'selected="selected"'; ?>><?php _e($l); ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>

      <?php if( count($field['classes']) ): ?>
        <label class="inline"><?php _e('Style'); ?></label>
        <select name="<?php echo $field['name']; ?>[class]">
          <option value="" <?php if(empty($class)) echo 'selected="selected"'; ?>><?php _e('Default'); ?></option>
          <?php foreach( $field['classes'] as $c => $l ): ?>
            <option value="<?php echo $c; ?>" <?php if($c == $class) echo 'selected="selected"'; ?>><?php _e($l); ?></option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
    <?php endif; 
	}


	

  /**
   * Presave save the options for the field
   */
  function pre_save_field($field) {
    // Convert styles into array
    $classes_lines = explode("\n", $field['classes']);
    $classes = array();
    foreach ($classes_lines as $line) {
      $p = explode( ' : ', trim($line) );
      if( count($p) > 1 ) {
        $classes[ $p[0] ] = $p[1];
      } elseif( strlen($p[0]) > 0 ) {
        $classes[ $p[0] ] = $p[0];
      } // else don't put empty lines in
    }
    $field['classes'] = $classes;


    return parent::pre_save_field($field);
  }
	

	
  /**
   * Include stylesheet
   */
	function admin_print_styles()	{
		$url = plugins_url('', ACF_LINK_FIELD_FILE).'/acf-link-field.css';
    wp_enqueue_style( 'acf-link-field', $url);
	}


	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*	- called from your template file when using the API functions (get_field, etc). 
	*	This function is useful if your field needs to format the returned value
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field){
		// get value
		$value = $this->get_value($post_id, $field);
		
		// return value
    if( !empty($value) ) {
		  return new ACF_Link_Field_Value($value);
    } else {
      return '';
    } 

	}
}

class ACF_Link_Field_Value {

  function __construct( $field ) {

    $field = array_merge(array(
      'url' => '',
      'text' => '',
      'class' => '',
      'target' => '',
      'title' => '',
    ), $field);
    foreach ($field as $key => $value) {
      $this->$key = $value;
    }

  }

  function __toString() {
    if( empty($this->url) && empty($this->text) ) {
      return '';
    }

    $o = '<a href="'.$this->url.'"';

    if( empty($this->class) ) {
      $o .= ' class="'.$this->class.'"';
    }

    if( empty($this->target) ) {
      $o .= ' target="'.$this->target.'"';
    }

    if( empty($this->title) ) {
      $o .= ' title="'.$this->title.'"';
    }
    $o .= '>'.$this->text.'</a>';


    return $o;
  }


} 