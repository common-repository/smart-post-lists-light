<?php
/**
 * Process otw actions
 *
 */
if( otw_post('otw_action',false) ){

	switch( otw_post('otw_action','') ){
		
		case 'otw_spll_manage_options':
				
				if( otw_post( 'otw_spl_promotions', false ) && !empty( otw_post( 'otw_spl_promotions', '' ) ) ){
					
					global $otw_spll_factory_object, $otw_spll_plugin_id;
					
					update_option( $otw_spll_plugin_id.'_dnms', otw_post( 'otw_spl_promotions', '' ) );
					
					if( is_object( $otw_spll_factory_object ) ){
						$otw_spll_factory_object->retrive_plungins_data( true );
					}
				}
				wp_redirect( admin_url( 'admin.php?page=otw-spl&message=1' ) );
			break;
	}
}
