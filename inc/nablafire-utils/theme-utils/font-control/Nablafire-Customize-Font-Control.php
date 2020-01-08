<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;

class Nablafire_Customize_Font_Control extends WP_Customize_Control
{
    public function __construct(
      $manager, $option, $args = array(), $data_keys = array(), $font_gen)
    {
        parent::__construct( $manager, $option, $args );                   

        $this->option     = $option;
        $this->data_keys  = $data_keys;
        $this->font_gen   = $font_gen;
    }

    // Font family
    public function font_template($key, $data){ ?>

      <span class="customize-control-title">
          <?php echo array_key_exists('label', $data) ?  
              $data['label'] : __('Font Family', 'pagoda'); ?>
      </span>

      <span class="customize-control-description">
          <?php echo array_key_exists('desc', $data) ? $data['desc'] : ''; ?>
      </span>

      <p>
        <select class="customize-font-family-control" 
                id="<?php echo str_replace('_','-', $this->option . $key) ?>"
                    <?php $this->link( $key ); ?> > 
                    <?php foreach($this->font_gen->get_fontlist() as $font_fam): ?>               
                    <option value="<?php echo esc_attr($font_fam); ?>" 
                    <?php echo selected($this->value( $key ), $font_fam, false) ?> >
                        <?php echo esc_attr($font_fam) ; ?>                    
                    </option>
                    <?php endforeach; ?>
        </select>
      </p>

    <?php }

    // Font variant
    public function variant_template($key, $data){ ?>

      <span class="customize-control-title">
        <?php echo array_key_exists('label', $data) ?  
                $data['label'] : __('Font Variant', 'pagoda') ?>
      </span>

      <span class="customize-control-description">
          <?php echo array_key_exists('desc', $data) ? $data['desc'] : ''; ?>
      </span>
      
      <p>
        <select class="customize-font-variant-control"
                id="<?php echo str_replace('_','-', $this->option . $key) ?>"
                <?php $this->link( $key ); ?> > 
                <?php foreach($this->font_gen->get_variants($this->value($data['font'])) as $font_var): ?>
                    <option value='<?php echo esc_attr($font_var); ?>' 
                    <?php echo selected($this->value( $key ), $font_var, false) ?> >
                      <?php echo esc_attr($font_var) ; ?>                                   
                    </option>
                <?php endforeach; ?>
        </select>
      </p>

  <?php }

  // Font size
  public function size_template($key, $data){ ?>
   
    <span class="customize-control-title">
      <?php echo array_key_exists('label', $data) ?  
              $data['label'] : __('Font Size (px)', 'pagoda') ?>
    </span>

    <span class="customize-control-description">
      <?php echo array_key_exists('desc', $data) ? $data['desc'] : ''; ?>
    </span>
    
    <p>
      <input class="customize-font-size-control" 
              id="<?php echo str_replace('_','-', $this->option . $key) ?>"
              <?php $this->link( $key ); ?>  
              type="number" 
              min="<?php echo esc_attr($data['range']['min']) ?>" 
              max="<?php echo esc_attr($data['range']['max']) ?>" 
              step="1"   
              value="<?php echo esc_attr($this->value( $key )); ?>" />
    </p>          

  <?php }

  // Font Color
  public function color_template($key, $data){ ?>

    <span class="customize-control-title">
      <?php echo array_key_exists('label', $data) ?  
              $data['label'] : __('Font Color (rgba)', 'pagoda') ?>
    </span>
    
    <span class="customize-control-description">
      <?php echo array_key_exists('desc', $data) ? $data['desc'] : ''; ?>
    </span>

    <p>
      <input class="color-picker customize-color-picker customize-font-color-control" 
             id="<?php echo str_replace('_','-', $this->option . $key) ?>"
             <?php $this->link( $key ); ?>  
             type="text" 
             data-default-color="<?php echo esc_attr( $data['default'] ) ?>" /> 
    </p>

  <?php }

  public function render_content(){ ?>

    <div style="padding:10px; border: 2px solid #BBBBBB; background-color:#efd3ff">
        
    <?php if ($this->label): // echo label if it exists ?>      
        <span class="customize-control-title"><?php echo $this->label; ?></span>
    <?php endif; ?>

    <?php if ($this->description): // echo description if it exists ?> 
        <span class="description customize-control-description"><?php echo $this->description; ?></span>
    <?php endif; ?>  

    <?php // We will only render content if a settings field has been passed. This allows
          // us to create reduced font controls which omit size, variant, color etc ... 
          
          foreach ($this->data_keys as $key => $data) { 
            if ($data['type'] == 'font')    {$this->font_template($key, $data);}
            if ($data['type'] == 'size')    {$this->size_template($key, $data);}
            if ($data['type'] == 'color')   {$this->color_template($key, $data);}
            if ($data['type'] == 'variant') {$this->variant_template($key, $data);}
          }
    ?>
       
   <?php }
}