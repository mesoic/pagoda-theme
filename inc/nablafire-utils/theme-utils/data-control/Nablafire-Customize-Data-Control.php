<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
  
if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;

class Nablafire_Customize_Data_Control extends WP_Customize_Control
{
  
    public function __construct(
      $manager, $option, $args = array(), $data_keys = array(), $color = 'grey') {

        parent::__construct( $manager, $option, $args );
        $this->option     = $option;
        $this->data_keys  = $data_keys;
        $this->box_colors = array(
          'red'   => '#ffebe0',
          'orange'=> '#ffeabf',
          'yellow'=> '#fcf5cc',
          'green' => '#d8ffda',
          'blue'  => '#dbddff',
          'purple'=> '#efd3ff',
          'grey'  => '#dddddd',
          'none'  => '#eeeeee',
      );

      $this->color = $this->box_colors[$color];
    }

    // Text Control Template
    public function text_template($key, $data){ ?>
    
        <p><span class="customize-control-title"><?php echo $data['label'] ?></span>
       
        <?php if (array_key_exists('desc', $data)): ?>            
          <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
        <?php endif; ?>
    
        <input class="customize-data-control" 
                  id="<?php echo str_replace('_','-', $this->option ) ?>" 
                  value="<?php echo esc_attr($this->value($key)); ?>"   
                  type="text" <?php $this->link($key); ?>  
                  />
          </p>
    
    <?php }

    // Number Template 
    public function number_template($key, $data){ ?>
    
        <p><span class="customize-control-title"><?php echo $data['label'] ?></span>
        
        <?php if (array_key_exists('desc', $data)): ?>            
          <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
        <?php endif; ?>
        
        <input class="customize-data-control" 
                id="<?php echo str_replace('_','-', $this->option ) ?>"
                <?php $this->link( $key ); ?>  
                type="number" 
                min="<?php echo esc_attr($data['range']['min']) ?>" 
                max="<?php echo esc_attr($data['range']['max']) ?>" 
                step="1"   
                value="<?php echo esc_attr($this->value( $key )); ?>" 
                />
        </p>
    
    <?php }

    // Select Control Template
    public function select_template($key, $data){ ?>
    
        <p><span class="customize-control-title"><?php echo $data['label'] ?></span>
    
        <?php if (array_key_exists('desc', $data)): ?> 
          <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
        <?php endif; ?>
    
        <?php $keyed = array_key_exists('keyed', $data) ? (bool)$data['keyed'] : false; ?>
        
        <select class="customize-data-control"
                id="<?php echo str_replace('_','-', $this->option ) ?>"
                <?php $this->link($key); ?> >
                <?php foreach($data['values'] as $_ => $value): ?>
                    <option value="<?php echo $keyed ? esc_attr($_) : esc_attr($value); ?>"   
                    <?php if(strcmp($this->value($key), $value) == 0) {echo 'selected="selected"'; } ?> >
                    <?php echo $value ?> 
                    </option>
                <?php endforeach; ?>
        </select>
        
        </p>   
      
    <?php }

    // Color Control Template
    public function color_template($key, $data){ ?>
          
      <p><span class="customize-control-title"><?php echo $data['label'] ?></span>
        
      <?php if (array_key_exists('desc', $data)): ?>            
        <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
      <?php endif; ?>        
        
      <input class="color-picker customize-color-picker customize-data-color-control" 
              id="<?php echo str_replace('_','-', $this->option ) ?>"
              <?php $this->link($key); ?>  
              type="text"
              data-default-color="<?php echo esc_attr($data['default']) ?>" />
        
      </p>
      
    <?php }

    // Checkbox Control Template
    public function checkbox_template($key, $data){ ?>

      <p><span class="customize-control-title">
         
      <input class="customize-data-control" 
              id="<?php echo str_replace('_','-', $this->option ) ?>"
              <?php $this->link($key); ?>  
              type="checkbox"   
              value="<?php echo esc_attr($this->value($key)); ?>" />
          
      <?php //inline label ?>
      <?php if (array_key_exists('label', $data)): ?><?php echo $data['label'] ?><?php endif; ?>
      </span></p>
      
      <?php if (array_key_exists('desc', $data)): ?>            
        <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
      <?php endif; ?> 
      
    <?php }

    // Label Template. 
    public function label_template($key, $data){ ?>       
        
      <p>
      <?php if (array_key_exists('label', $data)): ?>
        <span class="customize-control-title"><?php echo $data['label'] ?></span>
      <?php endif; ?> 
 
      <?php if (array_key_exists('desc', $data)): ?>            
        <span class="description customize-control-description"><?php echo $data['desc']; ?></span>
      <?php endif; ?>      
      </p>
    
    <?php }

    // Render the content on the theme customizer page
    public function render_content(){ ?>
         
      <?php // Div Properties ?>
      <div style="padding:10px; border: 2px solid #BBBBBB; background-color:<?php echo $this->color ?>">
       
      <?php if ($this->label): // echo label if it exists ?>      
        <span class="customize-control-title"><?php echo $this->label; ?></span>
      <?php endif; ?>

      <?php if ($this->description): // echo description if it exists ?> 
      	<span class="description customize-control-description"><?php echo $this->description; ?></span>
      <?php endif; ?>

      <?php foreach ($this->data_keys as $key => $data) { 
            if ($data['type'] == 'text') {$this->text_template($key, $data);}
            if ($data['type'] == 'color') {$this->color_template($key, $data);}
            if ($data['type'] == 'label') {$this->label_template($key, $data);}
            if ($data['type'] == 'number') {$this->number_template($key, $data);}
            if ($data['type'] == 'select') {$this->select_template($key, $data);}
            if ($data['type'] == 'checkbox') {$this->checkbox_template($key, $data);}
      } ?>
      </div>

    <?php }  
} // END Class