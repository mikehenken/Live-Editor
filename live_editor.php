<?php
/*
Plugin Name: Live Editor 1.0
Description: Allow site admins to edit website from the front end
Version: 1.0
Author: Mike Henken
Author URI: http://michaelhenken.com/
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 
	'Live Editor', 
	'1.0', 			
	'Mike Henken',	
	'http://michaelhenken.com/', 
	'Allow logged in site admins to edit website from the front end', 
	'settings', 
	'live_editor_admin' 
);

# hooks

//Show aloha wrapper and check submitted changes

add_action('content-top','le_before_content');

//End aloha wrapper
add_action('content-bottom','le_after_content');

//Display admin bar
add_action('theme-footer','le_admin_bar');

//Initialize Javascript & CSS
add_action('theme-header','le_show_aloha');

//Define Live Editor Settings File
define('LeFile', GSDATAOTHERPATH  . 'live_editor.xml');

/** 
* Admin settings - Not currently being used 
*
* @return void
*/ 
function live_editor_admin()
{

}

/** 
* Initialize Javascript & CSS - Submit data via ajax
*
* @return void
*/ 
function le_show_aloha()
{
	if(get_cookie('GS_ADMIN_USERNAME') != "")
	{
		global $SITEURL;
		?>
			<link href="<?php echo $SITEURL; ?>plugins/live_editor/css/le_front_end_css.css" type="text/css" rel="stylesheet" />
			<link href="<?php echo $SITEURL; ?>plugins/live_editor/aloha/css/aloha.css" type="text/css" rel="stylesheet" />
			<script>
				var Aloha = window.Aloha || ( window.Aloha = {} );
				
				Aloha.settings = {
					locale: 'en',
					plugins: {
						format: {
							config: [  'b', 'i', 'p', 'sub', 'sup', 'del', 'title', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'removeFormat' ],
						  	editables : {
								// no formatting allowed for title
								'#title'	: [ ]
						  	}
						},
						link: {
							editables : {
								// No links in the title.
								'#title'	: [  ]
						  	}
						},
						list: {
							editables : {
								// No lists in the title.
								'#title'	: [  ]
						  	}
						},
						image: {
							'fixedAspectRatio': true,
							'maxWidth': 1024,
							'minWidth': 10,
							'maxHeight': 786,
							'minHeight': 10,
							'globalselector': '.global',
							'ui': {
								'oneTab': false
							},
							editables : {
								// No images in the title.
								'#title'	: [  ]
						  	}
						}
					},
					sidebar: {
						disabled: true
					}
				};
			</script>
		    <script src="<?php echo $SITEURL; ?>plugins/live_editor/aloha/lib/aloha.js" data-aloha-plugins="common/format,
			                        common/table,
			                        common/list,
			                        common/link,
			                        common/highlighteditables,
			                        common/block,
			                        common/undo,
			                        common/contenthandler,
			                        common/paste,
			                        common/horizontalruler,
			                        common/align,
			                        common/commands,
			                        common/abbr,
			                        extra/browser"></script>
			<script type="text/javascript">
			Aloha.ready( function() {

				// Make #content editable once Aloha is loaded and ready.
				Aloha.jQuery('#live_editor_content').aloha();

				Aloha.require( ['aloha', 'aloha/jquery'], function( Aloha, jQuery) {

					jQuery("#le_meta_tags").click(function(){
						jQuery(".le_meta_tags_container").show();
					});

					jQuery(".le_meta_tags_close").click(function(){
						jQuery(".le_meta_tags_container").fadeOut();
					});


					jQuery("#LeSubmit").click(function(){
						jQuery(".le_success").show();
						jQuery('.le_success').delay(4000).fadeOut();

						var content = Aloha.editables[0].getContents();

						var pageId = window.location.pathname;

						var metaKeywords = jQuery(".le_meta_keywords").val(); 

						var metaDescription = jQuery(".le_meta_description").val(); 

						var request = jQuery.ajax({
							url: "<?php get_site_url(); ?><?php get_page_slug(); ?>",
							type: "POST",
							data: {
								content : content,
								metaKeywords : metaKeywords,
								metaDescription : metaDescription
							},
							dataType: "html"
						});
						request.done(function(msg) {
							jQuery("#log").html( msg );
						});
						request.fail(function(jqXHR, textStatus) {
							alert( "Request failed: " + textStatus );
						});
					});
				});
			});
		</script>
		<?php
	}
}


/** 
* Admin Display aloha wrapper and check for submitted changes
*
* @return void
*/ 
function le_before_content()
{
	if(get_cookie('GS_ADMIN_USERNAME') != "")
	{
		$request = '';
		$pageslug = return_page_slug();
		$file = GSDATAPAGESPATH.$pageslug.'.xml';
		if(isset($_REQUEST['content']))
		{
			$pageData = file_get_contents($file);
			$request = $_REQUEST['content'];
			$metaKeywords = $_REQUEST['metaKeywords'];
			$metaDescription = $_REQUEST['metaDescription'];

			$xml = new SimpleXMLExtended($pageData);
			$xml->content = '';
			$xml->meta = '';
			$xml->metad = '';
			$content = $xml->content;
			$content->addCData($request);
			$meta = $xml->meta;
			$meta->addCData($metaKeywords);
			$metad = $xml->metad;
			$metad->addCData($metaDescription);
			XMLsave($xml, $file);
		}
		echo '<div class="le_success" style="">The Page Has Been Succesfully Saved</div><div id="live_editor_content">';
	}
}

/** 
* Close Aloha Wrapper
*
* @return void
*/ 
function le_after_content()
{
	if(get_cookie('GS_ADMIN_USERNAME') != "")
	{
		echo '</div>';
	}
}

/** 
* Process Admin Settings - Not currently used
*
* @return void
*/ 
function le_process_page()
{

}

/** 
* Display admin bar 
*
* @return void
*/ 
function le_admin_bar()
{
	$pageslug = return_page_slug();
	$file = GSDATAPAGESPATH.$pageslug.'.xml';
	$xml = getXML($file);
	global $SITEURL;
	?>
	<div class="le_meta_tags_container" style="">
		<h2>Change Meta Tags</h2><br/>
		<p>
			<label>Keywords: </label>
			<input type="text" value="<?php echo $xml->meta; ?>" class="le_meta_keywords" name="le_meta_keywords" />
		</p><br/>
		<p>
			<label>Description: </label>
			<input type="text" value="<?php echo $xml->metad; ?>" class="le_meta_description" name="le_meta_description" />
		</p>
		<br/><a class="le_meta_tags_close" style="">Close</a>
		<div style="clear:both;"></div>
	</div>
	<div id="le_admin_bar" style="">
		<div id="le_admin_bar_wrapper" style="">
			<button id="le_meta_tags" style=""></button>
			<button id="LeSubmit" style=""></button>
		</div>
	</div>
	<?php
}

