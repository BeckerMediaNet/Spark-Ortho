<?php

namespace Level;

/**
 *
 */
class Block
{

	public array|false $fields;
	private array $block, $attr, $bg_img, $style, $class, $options;
	private bool $is_bg_dark, $is_full_height, $is_preview;
	private mixed $is_wide;
	private string $blockName, $name, $id, $bg_color;
	private int $style_level, $class_level, $attr_level;

	/**
	 * @param $block
	 */
	public function __construct($block, $options = [])
	{
		// init all vars
		$this->options        = $options;
		$this->style_level    = ($this->options['style_level'] ?? 0);
		$this->class_level    = ($this->options['class_level'] ?? 0);
		$this->attr_level     = ($this->options['attr_level'] ?? 0);
		$this->is_preview     = ($this->options['is_preview'] ?? false);
		$this->fields         = [];
		$this->block          = $block;
		$this->attr           = [0 => [], 1 => [], 2 => [], 3 => []];
		$this->bg_img         = [];
		$this->bg_color       = '';
		$this->is_full_height = false;
		$this->is_wide        = '';
		$this->style          = [0 => [], 1 => [], 2 => [], 3 => []];
		$this->class          = [0 => [], 1 => [], 2 => [], 3 => []];
		$this->blockName      = $block['name'] ?? 'default';
		//        $this->name = '';
		$this->id         = '';
		$this->is_bg_dark = false;

		// get block name
		$name = $block['name'] ?? ($block['blockName'] ?? 'default');
		// strip off "acf/" from name
		$this->name = preg_match('/\/(.*)/m', $name, $matches) ? $matches[1] : 'default';
		$this->id   = $this->setID();

		// add class for block name
		$this->addClass('block--' . $this->name);

		// Create class attribute allowing for custom "className" and "align" values.
		if ($this->block['className'] ?? false) {
			$this->addClass($this->block['className']);
		}

		// set acf fields into property
		if (function_exists('get_fields')) {
			$this->fields = get_fields();
		}

		$this->addClass('container', 'container');
		$this->addClass('row', 'row');
		$this->addClass('col-12', 'col');

		$this->generateStyling();
		//		$this->generateTrackingProps();
	}

	private function get_field($field, $default = '')
	{
		if ($this->fields[$field] ?? false) {
			if ($this->fields[$field]) {
				return $this->fields[$field];
			}
		}

		return $default;
	}

	/**
	 * @return bool
	 */
	public function getIsBgDark(): bool
	{
		return $this->is_bg_dark;
	}

	/**
	 * @return bool
	 */
	public function getIsWide(): mixed
	{
		return ($this->is_wide ?? false);
	}

	/**
	 * @param bool $is_wide
	 */
	public function setIsWide(bool $is_wide): void
	{
		$this->is_wide = $is_wide;

		if ($this->is_wide) {
			$this->removeClass('container', 1);
			// $this->addClass( 'container-fluid', 1 ); // TODO:review
		} else {
			// $this->removeClass( 'container-fluid', 1 ); // TODO:review
			$this->addClass('container', 1);
		}
	}

	/**
	 * @param $hex
	 *
	 * @return bool
	 */
	public function is_dark($hex): bool
	{
		if (! $hex || ! is_string($hex)) {
			return false;
		}
		if (str_contains($hex, 'url(')) {
			return false;
		}
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) === 3) {
			$result = '';
			for ($i = 0; $i < strlen($hex); $i++) {
				$result .= $hex[$i] . $hex[$i];
			}
			$hex = $result;
		}
		if (strlen($hex) === 6) {
			$c_r = hexdec(substr($hex, 0, 2));
			$c_g = hexdec(substr($hex, 2, 2));
			$c_b = hexdec(substr($hex, 4, 2));

			return ((($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000 < 125); // 0 - 255
		}

		return false;
	}

	/**
	 * @return array|mixed
	 */
	protected function getThemeJson(): mixed
	{
		$theme_json = [];

		if (get_transient('theme_json')) {
			$theme_json = get_transient('theme_json');
		} else {
			$theme_json_file = locate_template('theme.json', false, true);
			if ($theme_json_file) {
				$theme_json = json_decode(file_get_contents($theme_json_file), true);
				set_transient('theme_json', $theme_json, 60 * 60 * 24);
			}
		}

		return $theme_json;
	}

	protected function setPropClasses($classes = []): void
	{
		//    var_dump($classes);
		if (empty($classes)) {
			return;
		}

		foreach ($classes as $class) {
			$this->addClass($class);
		}
	}

	protected function generateTrackingProps(): void
	{
		$this->addAttribute(['data-component' => 'lvl:' . $this->name]);

		$metadata = $this->block['metadata'] ?? false;

		if ($metadata) {
			if ($metadata['name'] ?? false) {
				$this->addAttribute(['data-component-label' => esc_attr($metadata['name'])]);
			}
		}
	}

	/**
	 * @return void
	 */
	protected function generateStyling(): void
	{
		$this->setPropClasses($this->getProp('lVLBlock:classes') ?: []);

		// PROCESS NATIVE SPACING
		//		if ( $this->block['style']['spacing'] ?? false ) {
		//			$padding = $this->block['style']['spacing']['padding'] ?? [];
		//
		//			foreach ( $padding as $key => $value ) {
		//				$value = str_replace( ":", "(--wp--", $value );
		//				$value = str_replace( "|", "--", $value );
		//				$this->addStyle( 'padding-' . $key . ':' . $value . ');' );
		//			}
		//
		//			$margin = $this->block['style']['spacing']['margin'] ?? [];
		//
		//			foreach ( $margin as $key => $value ) {
		//				$value = str_replace( ":", "(--wp--", $value );
		//				$value = str_replace( "|", "--", $value );
		//				$this->addStyle( 'margin-' . $key . ':' . $value . ');' );
		//			}
		//		}


		// JS Props Processing
		//		$background = ( $this->getProp( 'background' ) ?: ( $this->getProp( 'style:color:background' ) ?: false ) );
		//
		//		if ( $background ) {
		//			if ( is_string( $background ) ) {
		//				$background = [ 'color' => $background ];
		//			}
		//
		//			$background['slug']  = $background['slug'] ?? 'custom';
		//			$background['color'] = $background['color'] ?? ( is_string( $background ) ? $background : 'transparent' );
		//			$background['name']  = $background['name'] ?? 'Custom';
		//
		//
		//			if ( $background['slug'] == 'custom' ) {
		//				$this->addStyle( 'background-color:' . ( $background['color'] ?? 'transparent' ) . ';' );
		//			} elseif ( ( $background['type'] ?? 'color' ) == 'color' ) {
		//				$this->addStyle( 'background-color:var(' . ( $background['slug'] ?? '--bg-missing' ) . ');' );
		//			} elseif ( ( $background['type'] ?? 'color' ) == 'gradient' ) {
		////				$this->addStyle( 'background-image:' . $background['color'] ?? '--gradient-missing' . ';' );
		//				$this->addClass( 'has' . $background['slug'] . '-gradient-background' );
		//			}
		//
		//			$this->bg_color   = ( $background['color'] ?? 'transparent' );
		//			$this->is_bg_dark = $this->is_dark( ( $background['color'] ?? false ) );
		//
		//			if ( str_contains( $background['slug'], '--dark' ) ) {
		//				$this->is_bg_dark = true;
		//			}
		//		}

		// WP Styles Processing
		//		if ( $this->block['backgroundColor'] ?? '' ) {
		//			$this->addStyle( 'background-color:var(' . $this->block['backgroundColor'] . ');' );
		//			$this->bg_color = ( $this->block['backgroundColor'] ?? 'transparent' );
		//
		//			$theme_json = $this->getThemeJson();
		//			if ( $theme_json ) {
		//				$color_palette = $theme_json['settings']['color']['palette'] ?? [];
		//
		//				foreach ( $color_palette as $color ) {
		//					if ( $color['slug'] == $this->block['backgroundColor'] ) {
		//						$this->is_bg_dark = $this->is_dark( $color['color'] );
		//
		//						break;
		//					}
		//				}
		//			}
		//		}

		// SPACING
		//		$vPadding = $this->getProp( 'verticalPadding' );
		//		$vMargin  = $this->getProp( 'verticalMargin' );
		//		if ( $vPadding ) {
		//			$this->setSpacing( $vPadding, 'padding' );
		//		}
		//		if ( $vMargin ) {
		//			$this->setSpacing( $vMargin, 'margin' );
		//		}
		//
		$this->setIsWide((($this->block['align'] ?? false) === 'full'));

		if ($this->is_full_height) {
			$this->addClass('full-height');
		}


		// COLOR CONTRAST
		//		if ( $this->is_bg_dark ) {
		//			$this->addAttribute( [ 'data-bs-theme' => 'dark' ] );
		//		} elseif ( $this->block['backgroundColor'] ?? '' || $background ) { // TODO: review if this covers all cases
		//			$this->addAttribute( [ 'data-bs-theme' => 'light' ] );
		//		}


		// ANIMATION
		// if ( $this->getField( 'is_animation' ) ) {
		// 	$animation = ( $this->getField( 'animation' ) ?: '' ) . '-';
		// 	$animation .= ( $this->getField( 'animation' ) == 'zoom' && $this->getField( 'zoom_direction' ) ? $this->getField( 'zoom_direction' ) . '-' : '' );
		// 	$animation .= ( $this->getField( 'direction' ) ?: '' );

		// 	$this->addAttribute(
		// 		[
		// 			'data-aos'          => $animation,
		// 			'data-aos-duration' => $this->getField( 'aos_duration' ),
		// 		],
		// 		'container'
		// 	);
		// }

		$this->addClass(
			($this->block['align'] ?? false
				? 'align' . $this->block['align']
				: '')
		);
		$this->addClass(
			($this->block['outline'] ?? false
				? 'has-font-style-outline'
				: '')
		);


		//containerFluid
		if ($this->block['containerFluid'] ?? false) {
			$this->addClass('container-fluid', 1);
			$this->removeClass('container', 1);
		}

		$this->addClass(($this->block['align'] ?? false ? 'align' . $this->block['align'] : ''));

		// Background color
		//		if ( $this->block['backgroundColor'] ?? false ) {
		//
		//			( $this->block['backgroundColor'] ) ? $this->addClass( 'has-background has-' . $this->block['backgroundColor'] . '-background-color' ) : '';
		//
		//			$bgColor = $this->getThemeColor( $this->block['backgroundColor'] );
		//			$this->addAttribute( [ 'data-bs-theme' => ( $this->is_dark( $bgColor ) ? 'dark' : 'light' ) ] );
		//
		//		}

		// Gradient background
		//		if ( $this->block['gradient'] ?? false ) {
		//			( $this->block['gradient'] ) ? $this->addClass( 'has-background has-' . $this->block['gradient'] . '-gradient-background' ) : '';
		//
		//			$this->addAttribute( [ 'data-bs-theme' => ( ( $this->block['gradient'] == 'green' ) ? 'dark' : 'light' ) ] );
		//		}

		// Box Shadow
		if ($this->block['style']['shadow'] ?? false) {
			$value = $this->block['style']['shadow'];
			$value = str_replace(":", "(--wp--", $value);
			$value = str_replace("|", "--", $value);
			$this->addStyle('box-shadow: ' . $value . ');');
		}

		// Border
		if (! $this->is_preview) {
			$this->setTextColor();
			$this->setBackground();
			$this->setBorder();
			$this->setSpacing();
			$this->setTextAlignment();
			$this->setFontSize();
			$this->setFontFamily();
		} else {
			//            $this->setIsDark(); // TODO: might not need because of editor applying theme
		}
	}

	//    protected function setIsDark() {
	//        $bgColor = $this->getProp( 'backgroundColor' );
	//        if ( $bgColor ) {
	//            $this->is_bg_dark = $this->is_dark( $bgColor );
	//
	//            if ( $this->is_bg_dark ) {
	//                $this->addAttribute( [ 'data-bs-theme' => 'dark' ] );
	//            } else {
	//                $this->addAttribute( [ 'data-bs-theme' => 'light' ] );
	//            }
	//        }
	//    }

	/**
	 * @return void
	 */
	protected function setSpacing(): void
	{
		$spacing = $this->getProp('style:spacing');

		if ($spacing) {
			$padding = $spacing['padding'] ?? [];
			$margin  = $spacing['margin'] ?? [];

			foreach ($padding as $key => $value) {
				$this->addStyle('padding-' . $key . ':' . $this->getSettingValue($value) . ';');
			}

			foreach ($margin as $key => $value) {
				$this->addStyle('margin-' . $key . ':' . $this->getSettingValue($value) . ';');
			}
		}
	}

	protected function getSettingValue($setting)
	{
		// if is numeric, return px, if preset, process preset, else return value
		if (is_numeric($setting)) {
			return $setting . 'px';
		} elseif (str_contains($setting, 'var:')) {
			// convert "var:preset|spacing|4" to "var(--wp--preset--spacing--4)"
			$setting = str_replace("var:", "var(--wp--", $setting) . ")";
			return str_replace("|", "--", $setting);
		} else {
			return $setting;
		}
	}

	/**
	 * @return void
	 */
	protected function setTextColor(): void
	{
		$color = $this->getProp('textColor');

		if ($color) {
			$this->addClass('has-text-color has-' . $color . '-color');
		}
	}

	/**
	 * @return void
	 */
	protected function setBackground(): void
	{
		$background = $this->getProp('backgroundColor');

		if ($background) {
			$this->addClass('has-background has-' . $this->block['backgroundColor'] . '-background-color');

			$bgColor = $this->getThemeColor($background);
			$this->addAttribute(['data-bs-theme' => ($this->is_dark($bgColor) ? 'dark' : 'light')]);
		}

		if ($this->block['backgroundImage'] ?? false) {
			$this->bg_img = $this->block['backgroundImage'];
			$this->addStyle('background-image:url(' . $this->bg_img['url'] . ');');
			$this->addStyle('background-size:' . ($this->bg_img['size'] ?? 'cover') . ';');
			$this->addStyle('background-position:' . ($this->bg_img['position'] ?? 'center') . ';');
			$this->addStyle('background-repeat:' . ($this->bg_img['repeat'] ?? 'no-repeat') . ';');
			$this->addStyle('background-attachment:' . ($this->bg_img['attachment'] ?? 'scroll') . ';');
		}
	}

	/**
	 * @return array[]
	 */
	protected function setBorder(): array
	{
		$border_color  = $this->getProp('borderColor');
		$border_width  = $this->getProp('style:border:width');
		$border_style  = $this->getProp('style:border:style');
		$border_radius = $this->getProp('style:border:radius');

		$addedClasses = [];
		$addedStyles  = [];

		$positions = ['top', 'right', 'bottom', 'left'];

		if ($this->getProp('style:border') && !empty(array_intersect_key($this->getProp('style:border'), array_flip($positions)))) {

			foreach ($positions as $position) {

				if (array_key_exists($position, $this->getProp('style:border'))) {

					$border = $this->getProp('style:border:' . $position);

					$value = [
						'width' => $border['width'] ?? '0px',
						'style' => $border['style'] ?? 'solid',
						'color' => $border['color'] ?? 'transparent'
					];

					if (str_starts_with($value['color'], 'var')) {
						$value['color'] = str_replace(":", "(--wp--", $value['color']);
						$value['color'] = str_replace("|", "--", $value['color']) . ')';
					}

					$this->addStyle('border-' . $position . ': ' . implode(' ', $value) . ';');
				}
			}
		}

		if ($border_color) {
			$this->addClass('has-border-color has-' . $border_color . '-border-color');
		}

		if ($border_width) {
			$style = 'border-width: ' . $border_width . ';';
			$this->addStyle($style);
			$addedStyles[] = $style;
		}

		if ($border_style) {
			$style = 'border-style: ' . $border_style . ';';
			$this->addStyle($style);
			$addedStyles[] = $style;
		}

		if ($border_radius) {
			if (is_array($border_radius)) {
				foreach ($border_radius as $key => $value) {
					$key   = preg_replace('/([a-z])([A-Z])/', '$1-$2', $key);
					$style = 'border-' . $key . '-radius: ' . $value . ';';
					$this->addStyle($style);
					$addedStyles[] = $style;
				}
			} else {
				$style = 'border-radius: ' . $border_radius . ';';
				$this->addStyle($style);
				$addedStyles[] = $style;
			}
		}

		return [
			'classes' => $addedClasses,
			'styles'  => $addedStyles,
		];
	}

	/**
	 * @return string
	 */
	protected function setTextAlignment(): void
	{

		$textAlignment = $this->getProp('style:typography:textAlign');

		if ($textAlignment) {
			$this->addClass('has-text-align-' . $textAlignment);
		}
	}

	/**
	 * @return string
	 */
	protected function setFontSize(): void
	{

		$fontSize = $this->getProp('fontSize');

		if ($fontSize) {
			$this->addClass('has-' . preg_replace('/([a-zA-Z])(\d)/', '$1-$2', $fontSize) . '-font-size');
		}
	}

	/**
	 * @return string
	 */
	protected function setFontFamily(): void
	{

		$fontFamily = $this->getProp('fontFamily');

		if ($fontFamily) {

			$this->addClass('has-' . $fontFamily . '-font-family');
		}
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{

		return $this->id;
	}

	protected function setID()
	{
		if ($this->block['anchor'] ?? false) {
			return $this->block['anchor'];
		}

		return uniqid('lvl-');
	}

	/**
	 * @return mixed|string
	 */
	public function getName(): mixed
	{
		return $this->name;
	}

	/**
	 * @return mixed|string
	 */
	public function getBlockName(): mixed
	{
		return $this->blockName;
	}

	/**
	 * @return array
	 */
	public function getBlock(): array
	{
		return $this->block;
	}


	// TODO: DEPRECATED
	/**
	 * @return bool
	 */
	public function has_intro(): bool
	{
		if ($this->get_field('intro_heading')) {
			return true;
		}
		if ($this->get_field('intro_text')) {
			return true;
		}

		return false;
	}

	public function has_parent(): bool
	{
		return ($this->block['hasParent'] ?? false);
	}


	/**
	 * @return mixed|string
	 */
	public function getBgImg(): mixed
	{
		return $this->bg_img;
	}

	/**
	 * @return mixed
	 */
	public function getBgColor(): mixed
	{
		return $this->bg_color;
	}

	public function getThemeColor($slug)
	{

		$theme = get_stylesheet_directory() . '/theme.json';

		if (file_exists($theme)) {
			$theme_json = json_decode(file_get_contents($theme), true);
			$palette    = $theme_json['settings']['color']['palette'];

			foreach ($palette as $color) {
				if ($color['slug'] == $slug) {
					return $color['color'];
				}
			}

			return null;
		}

		return null;
	}

	/** Mapping for level names to int values
	 *
	 * @param int|string $level
	 *
	 * @return string|int
	 */
	protected function get_mapped_level(int|string $level = 0): string|int
	{
		if (is_numeric($level)) {
			return $level; // already an int
		}
		if (is_string($level)) {
			$level = strtolower($level); // make lowercase
		}

		// map level text name to int value
		// default to 0 = section
		return match ($level) {
			'container' => 1,
			'row' => 2,
			'column', 'col' => 3,
			default => 0,
		};
	}

	/**
	 * Add attribute to block at a specific level
	 * the attributes are key value pairs that are output as html attributes
	 * where the key is the attribute name and the value is the attribute value
	 * e.g. ['data-attr' => 'value'] -> data-attr="value"
	 * The level can be set as a string or int where
	 * - 0 = section
	 * - 1 = container
	 * - 2 = row
	 * - 3 = col
	 *
	 * @param array           $attr key value pairs of attributes
	 * @param int|string|null $level level to add attribute to
	 *
	 * @return void
	 */
	public function addAttribute(array $attr, int|string $level = null): void
	{
		if (is_null($level)) {
			$level = $this->attr_level;
		}
		$level = $this->get_mapped_level($level);

		foreach ($attr as $key => $value) {
			$this->attr[$level][sanitize_key($key)] = esc_attr($value);
		}
	}

	/**
	 * @param string $attr
	 * @param        $level
	 *
	 * @return void
	 */
	public function removeAttribute(string $attr, $level = null): void
	{
		if (is_null($level)) {
			$level = $this->attr_level;
		}
		$level = $this->get_mapped_level($level);

		if (array_key_exists($attr, $this->attr[$level])) {
			unset($this->attr[$level][$attr]);
		}
	}

	/**
	 * @param $class array|string   class(es) to add
	 * @param $level string|int     0 = section, 1 = container, 2 = row, 3 = col
	 *
	 * @return void
	 */
	public function addClass(array|string $class, string|int $level = null): void
	{
		if (is_null($level)) {
			$level = $this->class_level;
		}
		$level = $this->get_mapped_level($level);

		if (! array_key_exists($level, $this->class)) {
			$this->class[$level] = [];
		}
		if (is_string($class)) {
			$this->class[$level] = array_merge($this->class[$level], explode(' ', $class));
		} elseif (is_array($class)) {
			$this->class[$level] = array_merge($this->class[$level], $class);
		}
		$this->class[$level] = array_unique($this->class[$level]);
	}

	/**
	 * @param string     $class
	 * @param string|int $level
	 *
	 * @return void
	 */
	public function removeClass(string $class, string|int $level = null): void
	{
		if (is_null($level)) {
			$level = $this->class_level;
		}
		$level = $this->get_mapped_level($level);

		if (! array_key_exists($level, $this->class)) {
			return;
		} // nothing to remove

		if (in_array($class, $this->class[$level])) {
			$this->class[$level] = array_filter($this->class[$level], function ($item) use ($class) {
				return ($item !== $class);
			});
		}
	}

	/**
	 * @param string|array $style
	 * @param int|string   $level
	 *
	 * @return void
	 */
	public function addStyle(string|array $style, int|string $level = null): void
	{
		if (is_null($level)) {
			$level = $this->style_level;
		}
		$level = $this->get_mapped_level($level);


		if (! array_key_exists($level, $this->style)) {
			$this->style[$level] = [];
		}

		if (is_string($style)) {
			// check if string ends in ";", if not, add it
			if (! str_ends_with($style, ';')) {
				$style .= ';';
			}
			$this->style[$level][] = $style;
		} elseif (is_array($style)) {
			// check if string ends in ";", if not, add it
			$style                 = array_map(function ($item) {
				if (! str_ends_with($item, ';')) {
					$item .= ';';
				}

				return $item;
			}, $style);
			$this->style[$level] = array_merge($this->style[$level], $style);
		}

		$this->style[$level] = array_unique($this->style[$level]);
	}

	/**
	 * @param string     $style
	 * @param int|string $level
	 *
	 * @return void
	 */
	public function removeStyle(string $style, int|string $level = null): void
	{
		if (is_null($level)) {
			$level = $this->style_level;
		}
		$level = $this->get_mapped_level($level);

		if (! array_key_exists($level, $this->style)) {
			return;
		} // nothing to remove

		$regex = '\b(' . $style . ')\s*:\s*([^;]+);\s*'; // matches style by property not value
		// if value in array matches regex, remove it
		if (is_array($this->style[$level])) {
			$this->style[$level] = array_filter($this->style[$level], function ($item) use ($regex) {
				return (! preg_match($regex, $item));
			});
		}
	}

	/**
	 * @param string      $content
	 * @param bool|string $section_wrap - false = no wrap, true = full wrap, 'basic' = wrap in one div
	 *
	 * If the block has a parent, it will be wrapped in a basic container, unless section_wrap is set to false.
	 *
	 * @return false|string
	 */
	public function renderSection(string $content = '', bool|string $section_wrap = 'full'): false|string
	{
		// if section wrap is string, make it lowercase
		if (is_string($section_wrap)) {
			$section_wrap = strtolower($section_wrap);
		}
		// if section wrap is full/true, but this block has a parent, set section wrap to basic
		// this is to prevent nesting too many containers
		if ($this->has_parent() && $section_wrap === 'full') {
			$section_wrap = 'basic';
		}

		ob_start();

		if ($section_wrap === 'full' || $section_wrap === true) { ?>
			<section <?php echo ($this->id) ? 'id="' . esc_attr($this->id) . '"' : ''; ?> class="block <?php echo esc_attr($this->getClass()); ?>" <?php echo $this->getStylesRender() ? ' style="' . esc_attr($this->getStylesRender()) . '"' : '';
																																							echo $this->getAttribute(); ?>>
				<div class="<?php echo $this->getClass('container'); ?>" <?php echo $this->getAttribute('container'); ?>>
					<div class="<?php echo $this->getClass('row'); ?>" <?php echo $this->getAttribute('row'); ?>>
						<div class="<?php echo $this->getClass('col'); ?>" <?php echo $this->getAttribute('col'); ?>>
							<?php echo $content; ?>
						</div>
					</div>
				</div>
			</section>
		<?php } elseif ($section_wrap === 'container') { ?>
			<div <?php echo ($this->id) ? 'id="' . esc_attr($this->id) . '"' : ''; ?> class="block <?php echo esc_attr($this->getClass()); ?>" <?php echo $this->getStylesRender() ? ' style="' . esc_attr($this->getStylesRender()) . '"' : '';
																																						echo $this->getAttribute(); ?>>
				<div class="<?php echo
							$this->getClass('container'); ?>" <?php echo $this->getAttribute('container'); ?>>
					<?php echo $content; ?>
				</div>
			</div>
		<?php } elseif ($section_wrap === 'basic') { ?>
			<div <?php echo $this->id ? ' id="' . esc_attr($this->id) . '"' : ''; ?> class="block <?php echo $this->getClass() ? esc_attr($this->getClass()) : ''; ?>" <?php echo $this->getStylesRender() ? ' style="' . esc_attr($this->getStylesRender()) . '"' : '';
																																											echo $this->getAttribute(); ?>>
				<?php echo $content; ?>
			</div>
		<?php } else {
			echo $content;
		}

		return ob_get_clean();
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function getAttribute(int|string $level = 0): string
	{
		$level = $this->get_mapped_level($level);

		if (isset($this->attr[$level])) {
			$attrs = '';
			foreach ($this->attr[$level] as $key => $item) {
				$attrs .= $key . '="' . $item . '" ';
			}

			return ($attrs ? ' ' . $attrs : '');
		}

		return '';
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function getClass(int|string $level = 0): string
	{
		$level = $this->get_mapped_level($level);

		if (isset($this->class[$level])) {
			return implode(' ', $this->class[$level]);
		}

		return '';
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function getStylesRender(int|string $level = 0): string
	{
		$level = $this->get_mapped_level($level);

		if (isset($this->style[$level])) {
			return implode('', $this->style[$level]);
		}

		return '';
	}

	/**
	 * @param string $prop
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function getStyle(string $prop, int|string $level = 0): string
	{
		$level = $this->get_mapped_level($level);

		$style = [];

		foreach ($this->style[$level] as $item) {
			$parts = explode(':', $item, 2);
			if (count($parts) == 2) {
				$key = trim($parts[0]);
				$value = trim($parts[1], " ;");
				$style[$key] = $value;
			}
		}

		return $style[$prop] ?? '';
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function renderStyle(int|string $level = 0): string
	{
		return ($this->getStylesRender($level) ? ' style="' . esc_attr($this->getStylesRender($level)) . '"' : '');
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function renderClass(int|string $level = 0): string
	{
		return ($this->getClass($level) ? ' class="' . esc_attr($this->getClass($level)) . '"' : '');
	}

	/**
	 * @param int|string $level
	 *
	 * @return string
	 */
	public function renderAttribute(int|string $level = 0): string
	{
		return ($this->getAttribute($level) ? ' ' . $this->getAttribute($level) : '');
	}


	/**
	 * @param string $field_name
	 *
	 * @return mixed
	 */
	public function getField(string $field_name): mixed
	{
		if (! function_exists('get_field')) {
			return false;
		}

		// if $selector in formation item:subitem format, get subitem
		if (str_contains($field_name, ':')) {
			$selector   = explode(':', $field_name);
			$field_name = $selector[0];
			$subitem    = $selector[1];
			$field      = get_field($field_name);
			if ($field) {
				return $field[$subitem] ?? false;
			}

			return false;
		}

		return get_field($field_name);
	}

	/**
	 * @return mixed|string
	 */
	public function getProp($prop): mixed
	{
		$keys  = explode(':', $prop);
		$value = $this->block;

		foreach ($keys as $key) {
			if (! isset($value[$key])) {
				return false;
			}
			$value = $value[$key];
		}

		return $value;
	}

	/**
	 * @param mixed|array $button
	 *
	 * @return string
	 */
	public function renderButton(mixed $button, array $options = []): string
	{
		if (empty($button)) {
			return '';
		}

		$output = '';
		$link   = ($button['link'] ?? '');
		$modal  = ($button['trigger_modal'] ?? '');

		if ($modal) {
			$anchor     = ($button['modal_anchor_link'] ?? '');
			$link_title = ($button['button_text'] ?? '');

			$classes = [];
			if ($options['class'] ?? '') {
				$classes[] = $options['class'];
				unset($options['class']);
			}
			$attributes = [];
			if ($options) {
				foreach ($options as $key => $value) {
					$attributes[] = $key . '="' . $value . '"';
				}
			}

			ob_start();
		?>
			<button type="button" class="btn btn-<?php echo ($button['style'] ?? ''); ?><?php echo (count($classes) > 0 ? ' ' . implode(' ', $classes) : ''); ?>"
				data-bs-toggle="modal" data-bs-target="<?php echo $anchor; ?>"
				<?php echo implode(' ', $attributes); ?>><?php echo $link_title; ?></button>
			<?php
			$output = ob_get_clean();
		} else {
			if ($link) {
				$link_url    = ($link['url'] ?? '');
				$link_target = ($link['target'] ?? '');
				$link_title  = ($link['title'] ?? '');

				$classes = [];
				if ($options['class'] ?? '') {
					$classes[] = $options['class'];
					unset($options['class']);
				}
				$attributes = [];
				if ($options) {
					foreach ($options as $key => $value) {
						$attributes[] = $key . '="' . $value . '"';
					}
				}

				$style = $button['style'] ?? 'primary';
				match ($style) {
					'link-arrow' => true, //do nothing
					'link' => $style = 'btn p-0 btn-' . $style,
					default => $style = 'btn btn-' . $style,
				};

				ob_start();
			?>
				<a href="<?php echo $link_url; ?>"
					target="<?php echo $link_target ?: '_self'; ?>"
					class="<?php echo $style; ?><?php echo (count($classes) > 0 ? ' ' . implode(' ', $classes) : ''); ?>"
					<?php echo implode(' ', $attributes); ?>><?php echo $link_title; ?><?php echo ($style === 'link-arrow' ? ' <svg xmlns="http://www.w3.org/2000/svg" width="18" height="15" viewBox="0 0 18 15" fill="none">
  <path d="M9.79279 1.46582L15.9999 7.4727L9.807 13.4658" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
  <path d="M16 7.47282H2" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
</svg>' : ''); ?></a>
		<?php
				$output = ob_get_clean();
			}
		}

		return $output;
	}

	//	function setSpacing( array $spacingProp, $type = 'padding' ): void {
	//		$type = substr( $type, 0, 1 );
	//
	//		foreach ( $spacingProp as $key => $value ) {
	//			if ( ! is_numeric( $value ) ) {
	//				continue;
	//			}
	//
	//
	//			$prefix   = $type . substr( $key, 0, 1 ) . '-';
	//			$modifier = '';
	//			if ( $value < 0 ) {
	//				$modifier = 'n';
	//				$value    = $value * - 1;
	//			}
	//
	//			$this->addClass( $prefix . 'lg-' . $modifier . $value );
	//
	//			if ( $value > 5 ) {
	//				$value = $value - 2;
	//			} elseif ( $value === 5 ) {
	//				$value = $value - 1;
	//			}
	//
	//			$this->addClass( $prefix . $modifier . $value );
	//
	//		}
	//	}

	/**
	 * @param $type - style of notice
	 * - info, warning, danger, success
	 * - default is info and displays inline-block
	 * @param $message - message to display
	 * @param $notice - optional - notice to display above message
	 *
	 * @return bool|string
	 */
	public function previewNotice($type = 'info', $message = '', $notice = ''): bool|string
	{
		$layout = 'block';
		if ($type === 'info') {
			$layout = 'inline-block';
		}

		$output = '';
		ob_start();
		?>
		<div class="d-<?php echo $layout; ?> text-body px-3 py-1 small bg-<?php echo esc_attr($type); ?>-subtle m-2 rounded border border-<?php echo esc_attr($type); ?>" data-bs-theme="light">
			<?php if ($notice): ?>
				<div class="bg-<?php echo esc_attr($type); ?> rounded px-3 py-1 mx-n2 mb-2"><strong><?php echo $notice; ?></strong></div>
			<?php endif; ?>
			<?php echo $message; ?>
		</div>
<?php
		return ob_get_clean();
	}
}
