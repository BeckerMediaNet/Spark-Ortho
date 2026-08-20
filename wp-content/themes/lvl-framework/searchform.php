<?php
$search_form = parse_blocks( '<!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search ...","width":100,"widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"isSearchFieldHidden":true} /-->' );

echo render_block( $search_form[0] ?? '' );