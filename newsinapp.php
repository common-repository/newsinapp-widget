<?php 
/*
Plugin Name: Newsinapp Widget
Plugin URI: http://newsinapp.io
Description: Show latest news about any topics you choose directly on your wordpress website.
Author: Newsinapp team
Version: 1.0
Author URI: http://newsinapp.io
*/
class Newsinapp_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'Newsinapp_Widget', // Base ID
			'Newsinapp Widget', // Name
			array( 'description' => __( 'Show latest news about any topics you choose directly on your wordpress website.', 'text_domain' ), ) // Args
		);

		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			wp_enqueue_script('newsinapp', plugins_url( 'newsinapp.js', __FILE__ ), array('jquery') );
			wp_register_style( 'newsinapp-widget-style', plugins_url('newsinapp.css', __FILE__) );
        	wp_enqueue_style( 'newsinapp-widget-style' );
		}
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		$this->print_news_javascript(false, $instance['publickey'], null, $instance['hiddennews'], $instance['maxnews'], $instance['topicfilter'], null);

		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['hiddennews'] =  $new_instance['hiddennews'];
		$instance['publickey'] =  $new_instance[ 'publickey' ];
		$instance['privatekey'] =  $new_instance[ 'privatekey' ];
		$instance['topicfilter'] =  $new_instance[ 'topicfilter' ];
		$instance['maxnews'] =  $new_instance[ 'maxnews' ];

		return $instance;
	}

	function print_news_javascript($admin_mode, $publickey, $privatekey, $hiddennews, $maxnews, $topicfilter, $topicfilterid)
	{
		?>
		<div class="newsinapp-news-form"></div>
		<script type="text/javascript">
			var newsinappWidgetConfig = {
	  			publicKey: '<?php echo $publickey ?>',
				<?php if($admin_mode) { ?>
	  			privateKey: '<?php echo $privatekey ?>',
	  			<?php } ?>
	  			domPlaceHolder:jQuery('.newsinapp-news-form')[0]
			};
			var nia = new newsinapp.Newsinapp(newsinappWidgetConfig.publicKey, newsinappWidgetConfig.privateKey);
			nia.news.getNews({
			    success: function (response, newsCollection) {
			    	var hiddenNews = {<?php
					if (is_array($hiddennews)) {
						foreach ($hiddennews as $cats) {
							echo "'" . $cats . "':1, ";
						}
					}
					?>};
					var maxNews = <?php echo $maxnews ?>;
			    	var str = '<div class="feed-box<?php if($admin_mode) { ?> admin<?php } ?>">';
			    	var newsShown = 0;
			    	for(newsIdx in newsCollection) {
			    		var news = newsCollection[newsIdx]; 
			    		if(news.topic.id != '<?php echo $topicfilter ?>' && '<?php echo $topicfilter ?>' != '-1')
			    			continue;
			    		<?php if($admin_mode) { ?>
			    		
				    		str += '<input id="<?php echo $this->get_field_id( 'hiddennews' ); ?>[]" name="<?php echo $this->get_field_name( 'hiddennews' ); ?>[]" value="' + news.publishId + '" type="checkbox" ';
				    		if(news.publishId in hiddenNews)
				    			str += 'checked="checked" />';
				    		else
				    			str += '/>';
				    		str += '<label>&nbsp;Hide this news</label>';

			    		<?php } else { ?>

			    			if(!(news.publishId in hiddenNews)) {
			    		<?php } ?>
			    		if(news.imageAvailable)
							str += '<div class="media"><div class="img-container"><img src="' + news.imageHref + '"></div>';
						else
			    			str += '<div class="media no-image">';
						str += '<a href="' + news.linkHref + '" target="_blank">' + news.title + '</a><p class="source">' + news.source + '</p><div class="footer"></div></div>';
						<?php if(!$admin_mode) { ?>
								newsShown++;
								if(newsShown >= maxNews)
			    					break;
			    			}

			    		<?php } ?>
					}
					str += "</div>"
					newsinappWidgetConfig.domPlaceHolder.innerHTML = str;
			    },
			    error: function (response) {console.log('Error getting newsinapp news'); console.log(response);},
			});

			<?php if($admin_mode) { ?>
			nia.topic.getTopics({
				success: function (response, topicCollection) {
					var selectList = jQuery('#<?php echo $topicfilterid ?>');
					for(topicIdx in topicCollection) {
			    		var topic = topicCollection[topicIdx]; 
			    		if(topic.id == '<?php echo $topicfilter ?>')
			    			selected = 'selected="selected"';
			    		else
			    			selected = '';
			    		selectList.append('<option value="' + topic.id + '" ' + selected + '>' + topic.displayName + ' (' + topic.entity[0] + ')</option>');
			    	}
				},
				error: function (response) {console.log('Error getting newsinapp topics'); console.log(response);},
			});
			<?php } ?>
			if(window.mixpanel == undefined) {
				(function(c,a){window.mixpanel=a;var b,d,h,e;b=c.createElement("script");b.type="text/javascript";b.async=!0;b.src=("https:"===c.location.protocol?"https:":"http:")+'//cdn.mxpnl.com/libs/mixpanel-2.0.min.js';d=c.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d);a._i=[];a.init=function(b,c,f){function d(a,b){var c=b.split(".");2==c.length&&(a=a[c[0]],b=c[1]);a[b]=function(){a.push([b].concat(Array.prototype.slice.call(arguments,0)))}}var g=a;"undefined"!==typeof f?g=a[f]=[]:f="mixpanel";g.people=g.people||[];h="disable track track_pageview track_links track_forms register register_once unregister identify name_tag set_config people.set people.increment".split(" ");for(e=0;e<h.length;e++)d(g,h[e]);a._i.push([b,c,f])};a.__SV=1.1})(document,window.mixpanel||[]);
				mixpanel.init("207cb9bb44c8dd4343131b06a044d445");
			<?php if($admin_mode) { ?>
				mixpanel.track("admin-widget-rendered");
			<?php } else { ?>
				mixpanel.track("widget-rendered");
			<?php } ?>
			}
		</script>
		<?php

	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Widget title', 'text_domain' );
		}
		if ( isset( $instance[ 'publickey' ] ) ) {
			$publickey = $instance[ 'publickey' ];
		}
		else {
			$publickey = '';
		}
		if ( isset( $instance[ 'privatekey' ] ) ) {
			$privatekey = $instance[ 'privatekey' ];
		}
		else {
			$privatekey = '';
		}
		if ( isset( $instance[ 'topicfilter' ] ) ) {
			$topicfilter = $instance[ 'topicfilter' ];
		}
		else {
			$topicfilter = '-1';
		}
		if ( isset( $instance[ 'maxnews' ] ) ) {
			$maxnews = $instance[ 'maxnews' ];
		}
		else {
			$maxnews = 6;
		}
		if ( isset( $instance[ 'hiddennews' ] ) ) {
			$hiddennews = $instance[ 'hiddennews' ];
		}
		else {
			$hiddennews = array();
		}

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'publickey' ); ?>"><?php _e( 'Newsinapp public key:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'publickey' ); ?>" name="<?php echo $this->get_field_name( 'publickey' ); ?>" type="text" value="<?php echo esc_attr( $publickey ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'privatekey' ); ?>"><?php _e( 'Newsinapp private key:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'privatekey' ); ?>" name="<?php echo $this->get_field_name( 'privatekey' ); ?>" type="text" value="<?php echo esc_attr( $privatekey ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'maxnews' ); ?>"><?php _e( 'Number of visible news:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'maxnews' ); ?>" name="<?php echo $this->get_field_name( 'maxnews' ); ?>" type="text" value="<?php echo esc_attr( $maxnews ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'topicfilter' ); ?>"><?php _e( 'Topic to show:' ); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'topicfilter' ); ?>" name="<?php echo $this->get_field_name( 'topicfilter' ); ?>" type="text" value="<?php echo esc_attr( $maxnews ); ?>">
				<option selected="selected" value="-1">All topics</option>
		</select>
		</p>

		<?php 
		
		if ($instance) {
			$this->print_news_javascript(true, $publickey, $privatekey, $hiddennews, $maxnews, $topicfilter, $this->get_field_id( 'topicfilter' ));
		}
		
	}

} // class Newsinapp_Widget

add_action( 'widgets_init', create_function( '', 'register_widget( "Newsinapp_Widget" );' ) );

?>