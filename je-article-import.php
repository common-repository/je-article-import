<?php
/*
Plugin Name: JE Article import
Plugin URI: http://www.tsvurach-handball.de
Description: Import articles from a custom user site
Version: 0.2.3
Author: Jens Ertel
Author URI: http://www.tsvurach-handball.de
License: GPLv2
*/

# *************************************
# Global variables
# *************************************
global $je_ai_db_version;
$je_ai_db_version = '0.2';

# *************************************
# Import Java script files
# *************************************
wp_enqueue_script('je-ai-js', plugins_url ('/js/je-article-import.js', __FILE__));

# *************************************
# Installation
# *************************************
function je_ai_install() {
	global $wpdb;
	global $je_ai_db_version;
	$tablename = $wpdb->prefix . 'je_ai';	
	$charset_collate = '';

	# *************************************
	# Installation 
	# *************************************
	If (!get_option('je_ai_db_version'))
	{
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$sql = "CREATE TABLE $tablename (		
			id int(11) NOT NULL AUTO_INCREMENT,
			date int(11) NOT NULL,
			author text NOT NULL,
			header text NOT NULL,
			body text NOT NULL,
			category int(11) NOT NULL,
			id_wp int(11) DEFAULT NULL,
			journal int(11) DEFAULT NULL,
			PRIMARY KEY (id)  
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		add_option( 'je_ai_db_version', $je_ai_db_version );
	}
}

# *************************************
# Create custom top-level menu
# *************************************
if ( ! function_exists( 'je_create_main_menu' ) )
{
	function je_create_main_menu()
	{
		add_menu_page ('JE Plugins', 'JE Plugins', 'manage_options', 'je_info_plugins', 'je_info_plugins');
	}
}

# *************************************
# Info plugin site
# *************************************
if ( ! function_exists( 'je_info_plugins' ) )
{
	function je_info_plugins()
	{
		?>
		<div class="wrap">
			<h2>JE Plugins</h2><p>
			Lieber Plugin Nutzer,<p>alle Plugins die von mir entwickelt worden sind, wurden aufgrund von Anforderungen meiner Tätigkeit als Administrator der Webseite des TSV Urach Abteilung Handball durchgeführt.<p>Der Nutzen der Funktion ist die Optimierung und Handhabung für nicht Administratoren zu erleichtern, die nicht über das notwendige Fachwissen Verfügen einen Blog oder Internetseite zu administrieren. Durch den Einsatz der Eigenentwicklungen, soll der Funktionsumfang so verbessert werden, dass viele Tätigkeiten automatisiert werden und der Webmaster mehr Zeit für wesentlichere Tätigkeiten übrig hat. Oftmals ist der Webmaster nicht nur in dieser Funktion für den Verein ehrenamtlich Tätig.<p>Im Falle des TSV Urach Abteilung Handball, werden Berichte von diversen Personen eingereicht. Diese müssen nun von einem nicht Webmaster geprüft, veröffentlicht und an Zeitungen verschickt werden. Anschließend müssen diese auch noch kopiert und an ein Gemeindeblatt verschickt werden.<p>In einem anderen Fall, wird ein anderes Plugin für die Termine verwendet die in der Abteilung anstehen. Da dieses Plugin keine Import Funktion zur Verfügung stellt um die vorliegenden Informationen vom Verband zu importieren, wurde auch dies so erweitert dass dies ermöglicht wird. Andernfalls sind viel Anpassung und Änderungen an den Rohdaten notwendig um dies zu ermöglichen.<p>Zuletzt gab es noch die Anforderung aktuelle Spielergebnisse und bevorstehende Paarungen auf der Homepage zu zeigen, bzw. anderweitig zur Verfügung zu stellen. Auch hier wurde ein Plugin entwickelt um an entsprechende Informationen leicht zu kommen.<p>Mit besten Grüßen<p>Jens Ertel<br>Ehrenamtlicher stellv. Abteilungsleiter<p>
			<div id="message" class="updated">
				Die Entwicklung meiner Plugins erfolgt rein ehrenamtlich zur Optimierung der Arbeitsabläufe und zur leichteren Integration von weiteren ehrenamtlichen Mitarbeitern.<p>Unterstützen auch Sie die Arbeit des Ehrenamtes beim TSV Urach Abteilung Handball aber auch in anderen Verein und Organisationen.<p>Die Abteilung Handball des TSV Urach, sowie Ich würden uns über eine Spende auf unser Vereinskonto freuen:<p>TSV Urach Abteilung Handball<br>Volksbank Metzingen-Bad Urach eG<br>IBAN: DE44640912000030951003<br>BIC: GENODES1MTZ<p>Unterstützen auch Sie diese Arbeit!
			</div>
		</div>
		<?php
	}
}

# *************************************
# Create submenu items
# *************************************
function je_ai_create_menu()
{
	add_submenu_page ('je_info_plugins', 'Berichte importieren', 'Berichte importieren', 'publish_posts', __FILE__, 'je__ai_importarticles');
}

# *************************************
# Add infos with new posts to admin bar
# *************************************
function je_ai_admin_bar( $wp_admin_bar )
{	
	function je_ai_articles_null()
	{
		global $wpdb;
		$tablename = $wpdb->prefix . "je_ai";
	
		$articles = $wpdb->get_results( $wpdb->prepare ( "SELECT * FROM $tablename WHERE id_wp IS NULL", $p_tablename), ARRAY_A);
		return count($articles);
	}
	
	$args = array(
		'title' => ('<img src="'.plugins_url('/images/sticky.png', __FILE__).'" style="vertical-align:middle;margin-right:10px" alt="'.je_ai_articles_null().' Berichte stehen zum Import bereit" title="'.je_ai_articles_null().' Berichte stehen zum Import bereit" />'.je_ai_articles_null().''),
		'href'  => get_admin_url().'admin.php?page=je-article-import/je-article-import.php&tab=articles',
		'meta'  => array( 'title' => je_ai_articles_null().' Berichte stehen zum Import bereit' )
	);
	$wp_admin_bar->add_node( $args );
}

# *************************************
# Import articles
# *************************************
function je__ai_importarticles()
{
	?>
	<div class="wrap">
		<h2>Berichte in WP importieren</h2><br/>
		<?php
		# Buttons for list articles or view options
		if( isset( $_GET[ 'tab' ] ) )
		{
			$active_tab = $_GET[ 'tab' ];
		}
		?>
		<h2 class="nav-tab-wrapper">
			<a href="?page=je-article-import/je-article-import.php&tab=articles" class="nav-tab <?php echo $active_tab == 'articles' ? 'nav-tab-active' : $active_tab == '' ? 'nav-tab-active' : ''; ?>">Berichte</a>
			<a href="?page=je-article-import/je-article-import.php&tab=options" class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>">Optionen</a>
		</h2>
		<?php	
		# Get articles that are not imported in WP
		if ( ( $_GET[ 'tab' ] == 'articles' ) )
		{
			if (isset($_POST["submit_import"]))
			{
				je_ai_listarticles_import($con);
				je_ai_listarticles($con);
			}
			elseIf (isset($_POST["submit_journal"]) OR isset($_POST["submit_journal_send"]))
			{
				je_ai_journal($con);
				if (isset($_POST["submit_journal_send"]))
				{
					je_ai_listarticles($con);
				}
			}
			elseIf (isset($_POST["submit_delete"]))
			{
				je_ai_listarticles_delete($con);
				je_ai_listarticles($con);
			}
			else
			{
				je_ai_listarticles($con);
			}
		}
		elseIf ( ( $_GET[ 'tab' ] == 'options' ) )
		{
			je_ai_options_page();
		}
		else
		{
			if (isset($_POST["submit_import"]))
			{
				je_ai_listarticles_import($con);
				je_ai_listarticles($con);
			}
			elseIf (isset($_POST["submit_journal"]) OR isset($_POST["submit_journal_send"]))
			{
				je_ai_journal($con);
				if (isset($_POST["submit_journal_send"]))
				{
					je_ai_listarticles($con);
				}
			}
			elseIf (isset($_POST["submit_delete"]))
			{
				je_ai_listarticles_delete($con);
				je_ai_listarticles($con);
			}
			else
			{
				je_ai_listarticles($con);
			}
		}
		?>
	</div>
	<?php
}

# *************************************
# List articles
# *************************************
function je_ai_listarticles()
{	
	global $wpdb;
	$tablename = $wpdb->prefix . "je_ai";
	
	$articles = $wpdb->get_results( $wpdb->prepare ( "SELECT * FROM $tablename ORDER BY date DESC", $p_tablename), ARRAY_A);

	?>
		<form method="post" action="">
		<p>
		<class="submit"><input type="submit" name="submit_import" id="submit" class="button button-primary" value="Import" />
		<class="submit"><input type="submit" name="submit_journal" id="submit" class="button button-primary" value="Zeitung" />
		<class="submit"><input type="submit" name="submit_delete" id="submit" class="button button-primary" value="Löschen" />
		</p>
			<table class="widefat">
			<thead>
				<tr>
					<th id="cb" class="manage-column column-cb check-column" scope="col" style="padding:0; text-align:left; vertical-align:middle;"><input type="checkbox" name="articles_extern_id[]" value="X"></th>
					<th width="10%">Datum</th>
					<th width="10%">Autor</th>
					<th width="10%">Kategorie</th>
					<th>Titel</th>
					<th width="5%">Import</th>
					<th width="5%">Online</th>
					<th width="5%">Zeitung</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th id="cb" class="manage-column column-cb check-column" scope="col" style="padding:0; text-align:left; vertical-align:middle;"><input type="checkbox" name="articles_extern_id[]" value="X"></th>
					<th width="10%">Datum</th>
					<th width="10%">Autor</th>
					<th width="10%">Kategorie</th>
					<th>Titel</th>
					<th width="5%">Import</th>
					<th width="5%">Online</th>
					<th width="5%">Zeitung</th>
				</tr>
			</tfoot>
			<tbody>
				<?
				foreach ($articles as $journal)
				{
					?>
					<tr class="alternate">
						<th class="check-column" scope="row" style="padding:0; text-align:left; vertical-align:middle;"><input type="checkbox" name="articles_extern_id[]" value="<?php echo $journal['id'] ?>"></th>
						<td width="10%"><?php echo date('d.m.Y', $journal["date"]) ?></td>
						<td width="10%"><?php echo $journal['author'] ?></td>
						<td width="10%"><?php echo get_the_category_by_ID( $journal['category'] ) ?></td>
						<td><?php echo $journal['header'] ?></td>
						<td width="5%">
						<?php
							If ($journal['id_wp'] != NULL)
							{
								?>
								<a href="<?php echo get_edit_post_link( $journal['id_wp'] ); ?>" target="_blank">
								<img src="<?php echo plugins_url('/images/dialog_yes.png', __FILE__); ?>" alt="YES">
								</a>
								<?php
							}
							else
							{
								?>
								<img src="<?php echo plugins_url('/images/dialog_no.png', __FILE__); ?>" alt="NO">
								<?php
							}
						?>
						</td>
						<td width="5%">
						<?php
							If ($journal['id_wp'] != NULL)
							{
								$post_status = get_post_field( 'post_status', $journal['id_wp']);
								If ($post_status == "publish")
								{
									?>
									<img src="<?php echo plugins_url('/images/dialog_yes.png', __FILE__); ?>" alt="YES">
									<?php
									
								}
								else
								{
									?>
									<img src="<?php echo plugins_url('/images/dialog_no.png', __FILE__); ?>" alt="NO">
									<?php
								}
							}
							else
							{
								?>
								<img src="<?php echo plugins_url('/images/dialog_no.png', __FILE__); ?>" alt="NO">
								<?php
							}
						?>
						</td>
						<td width="5%">
						<?php
							If ($journal['journal'] != NULL)
							{
								If ($journal['journal'] == 1)
								{
									?>
									<img src="<?php echo plugins_url('/images/dialog_yes.png', __FILE__); ?>" alt="YES">
									<?php
									
								}
								else
								{
									?>
									<img src="<?php echo plugins_url('/images/dialog_no.png', __FILE__); ?>" alt="NO">
									<?php
								}
							}
							else
							{
								?>
								<img src="<?php echo plugins_url('/images/dialog_no.png', __FILE__); ?>" alt="NO">
								<?php
							}
						?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			</table>
		</form>
	<?
}

# *************************************
# Import Articles from the list into WP
# *************************************
function je_ai_listarticles_import()
{
	global $wpdb;
	$tablename = $wpdb->prefix . "je_ai";
	$wpdb->je_ai = $wpdb->prefix . "je_ai";
	
	If ($_POST["articles_extern_id"] == NULL)
	{
		?>
		<div id="message" class="error">Keine Berichte für den Import ausgewählt.</div>
		<?php
		return;
	}
	
	foreach ($_POST["articles_extern_id"] as $id)
	{	
		If ($id != "X")
		{
			$article = $wpdb->get_row(  $wpdb->prepare ("SELECT * FROM $tablename WHERE id=$id", $p_tablename), ARRAY_A );
			
			If ($article['id_wp'] == NULL)
			{		
				
				$my_post = array('post_title' => $article['header'],'post_content' => $article['body'],'post_status' => 'draft','post_category' => array($article['category']),'post_date' => (date("Y-m-d H:i:s", $article['date'])));

				// Insert the post into the wp database
				$id_wp = wp_insert_post( $my_post );
				If ($id_wp != 0)
				{
					// Set thumbnail
					If (get_option('je_ai_setting_imageid_'.$article['category']) != NULL)
					{
						set_post_thumbnail( $id_wp, get_option('je_ai_setting_imageid_'.$article['category']) );
					}
					elseIf (get_option('je_ai_setting_imageid_default' ) != NULL)
					{
						set_post_thumbnail( $id_wp, get_option('je_ai_setting_imageid_default' ) );
					} // End set thumbnail

					$values = array('id_wp' => $id_wp);
					$where = array('id' => $id);
					$formats_values = array('%s');
					$formats_where = array('%d');
					If ($wpdb->update( $wpdb->je_ai, $values, $where, $formats_values, $formats_where) == FALSE)
					{
						?>
						<div id="message" class="error">Externe Datenbank konnte für Bericht "<?php echo $article['header']; ?>" mit der ID <?php echo $article['id_wp']; ?> nicht aktualisiert werden.</div>
						<?php
					}
				}
				else
				{
					?>
					<div id="message" class="error">Artikel konnte nicht importiert werden.</div>
					<?php
				}
			}
			else
			{
				?>
				<div id="message" class="error">Artikel "<?php echo $article['header']; ?>" wurde bereits importiert mit der ID <?php echo $article['id_wp']; ?>.</div>
				<?php
			}
		}
	}
}

# *************************************
# The journal site
# *************************************
function je_ai_journal($con)
{
	global $wpdb;
	$tablename = $wpdb->prefix . "je_ai";
	$tablename_options = $wpdb->prefix . "options";
	$wpdb->je_ai = $wpdb->prefix . "je_ai";
		
	if (isset($_POST["submit_journal_send"]))
	{
		$journal_count = 1;
		$option_mailreciptient = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename_options WHERE option_name LIKE 'je_ai_setting_mailreciptient_%%'", $p_tablename), ARRAY_A);
		
		while ($journal_count <= $_POST["journal_count"])
		{
			$mailreciptient_count = 0;
			
			$mail_subject = "Bericht TSV Urach Handball " . get_cat_name ($_POST["category$journal_count"]); 
			$mail_message = $_POST["journal$journal_count"];

			foreach ($option_mailreciptient as $i => $value)
			{
				$mail_to = $option_mailreciptient[$i]["option_value"];
				If (wp_mail ($mail_to, $mail_subject, $mail_message))
				{
					$id = $_POST["id$journal_count"];
					$article = $wpdb->get_row( $wpdb->prepare ( "SELECT * FROM $tablename WHERE id=$id", $p_tablename, $p_id), ARRAY_A);
					If ($article['journal'] == NULL)
					{
						$values = array('journal' => "1");
						$where = array('id' => $id);
						$formats_values = array('%d');
						$formats_where = array('%d');
						If ($wpdb->update( $wpdb->je_ai, $values, $where, $formats_values, $formats_where) == FALSE)
						{
							?>
							<div id="message" class="error">Externe Datenbank konnte für Bericht mit der ID <?php echo $id ?> nicht aktualisiert werden.</div>
							<?php
						}
					}
					$mailreciptient_count++;
				}
				else
				{
					?>
					<div id="message" class="error">E-Mail <?php echo $mail_subject; ?> konnte an <?php echo $mail_to; ?> nicht verschickt werden.</div>
					<?php
				}
			}
			$journal_count++;
		}
		?>
		<div id="message" class="updated">E-Mails wurden an die Empfänger erfolgreich verschickt.</div>
		<?php
	}
	else
	{
		If ($_POST["articles_extern_id"] == NULL)
		{
			?>
			<div id="message" class="error">Keine Berichte ausgewählt die versendet werden können.</div>
			<?php
			return;
		}
		
		?>
		<form method="post" action="">
		<p>
		<class="submit"><input type="submit" name="submit_journal_send" id="submit" class="button button-primary" value="Berichte verschicken" />
		</p>
		<?php
		$journal_count = 0;
		foreach ($_POST["articles_extern_id"] as $id)
		{
			If ($id != "X")
			{
				$journal_count++;
				$article = $wpdb->get_row( $wpdb->prepare ( "SELECT * FROM $tablename WHERE id=$id", $p_tablename, $p_id), ARRAY_A );			
				$journal = $article['header'] . "\n" . $article['body'] . "\n\n" . "Bericht von: " . $article['author'] . "\n" . get_option('je_ai_setting_seperator') . "\n\n";
				$journal = get_option('je_ai_setting_header') . "\n" . $journal_count_text . get_option('je_ai_setting_seperator') . "\n" . $journal . get_option('je_ai_setting_footer');
				?>
				<textarea name="journal<?php echo $journal_count; ?>" cols="100" rows="25"><?php echo $journal; ?></textarea>
				<input type="hidden" name="id<?php echo $journal_count; ?>" value="<?php echo $article['id']; ?>">
				<input type="hidden" name="category<?php echo $journal_count; ?>" value="<?php echo $article['category']; ?>">
				<?php
			}
		}
		?>
		<input type="hidden" name="journal_count" value="<?php echo $journal_count; ?>">
		</form>
		<?php
	}
}

# *************************************
# Delete the selected articles in the list view
# *************************************
function je_ai_listarticles_delete($con)
{
	global $wpdb;
	$tablename = $wpdb->prefix . "je_ai";
	$wpdb->je_ai = $wpdb->prefix . "je_ai";
	
	If ($_POST["articles_extern_id"] == NULL)
	{
		?>
		<div id="message" class="error">Keine Berichte zum löschen ausgewählt.</div>
		<?php
		return;
	}
	
	foreach ($_POST["articles_extern_id"] as $id)
	{
		If ($id != "X")
		{
			$article = $wpdb->get_row( $wpdb->prepare ( "SELECT * FROM $tablename WHERE id=$id", $p_tablename, $p_id), ARRAY_A );
			
			// Delete the post in the wp database
			If ($article['id_wp'] != NULL)
			{
				If ((wp_delete_post($article['id_wp'], $True)) == FALSE)
				{
					?>
					<div id="message" class="error">Artikel wurde mit der ID <?php echo $article['id_wp'] ?> konnte aus Wordpress nicht gelöscht werden.</div>
					<?php
				}
			}
			
			$where = array('id' => $id);
			$formats_where = array('%d');
			
			If ($wpdb->delete( $wpdb->je_ai, $where, $formats_where) == FALSE)
			{
				?>
				<div id="message" class="error">Artikel mit der ID <?php echo $id ?> konnte aus der Externen Datenbank nicht gelöscht werden.</div>
				<?php
			}
		}
	}
}

# *************************************
# The option page
# *************************************
function je_ai_options_page()
{		
	global $wpdb;
	$tablename = $wpdb->prefix . "options";
	
	if (isset($_POST["submit"]))
	{
		If (get_option('je_ai_setting_header') != $_POST[je_ai_setting_header])
		{
			If (update_option( 'je_ai_setting_header', $_POST[je_ai_setting_header] ) == FALSE)
			{
				?>
				<div id="message" class="error">Kopfzeile: Konnte nicht gespeichert werden.</div>
				<?php
			}
		}
		
		If (get_option('je_ai_setting_footer') != $_POST[je_ai_setting_footer])
		{
			If (update_option( 'je_ai_setting_footer', $_POST[je_ai_setting_footer] ) == FALSE)
			{
				?>
				<div id="message" class="error">Fußzeile: Konnte nicht gespeichert werden.</div>
				<?php
			}
		}
		
		If (get_option('je_ai_setting_seperator') != $_POST[je_ai_setting_seperator])
		{
			If (update_option( 'je_ai_setting_seperator', $_POST[je_ai_setting_seperator] ) == FALSE)
			{
				?>
				<div id="message" class="error">Trennzeichen: Konnte nicht gespeichert werden.</div>
				<?php
			}
		}
	
		// Default images
		If ($_POST[je_ai_setting_imageid_default] != NULL)
		{
			If ($_POST[je_ai_setting_imageid_default] != get_option('je_ai_setting_imageid_default'))
			{
				update_option( 'je_ai_setting_imageid_default', $_POST[je_ai_setting_imageid_default] );
			}
		}
		else
		{
			delete_option( 'je_ai_setting_imageid_default' );
		}
		
		If ($_POST[je_ai_setting_imageurl_default] != NULL)
		{
			If ($_POST[je_ai_setting_imageurl_default] != get_option('je_ai_setting_imageurl_default'))
			{
				update_option( 'je_ai_setting_imageurl_default', $_POST[je_ai_setting_imageurl_default] );
			}
		}
		else
		{
			delete_option( 'je_ai_setting_imageurl_default' );
		}
		// End default images
		
		// Category images
		$categories = get_categories();
		foreach ($categories as $category)
		{
			$cat_ID = $category->cat_ID;
			
			If ($_POST[je_ai_setting_imageid_.$cat_ID] != NULL)
			{
				If ($_POST[je_ai_setting_imageid_.$cat_ID] != get_option('je_ai_setting_imageid_'.$cat_ID))
				{
					update_option( 'je_ai_setting_imageid_'.$cat_ID, $_POST[je_ai_setting_imageid_.$cat_ID] );
				}
			}
			else
			{
				delete_option( 'je_ai_setting_imageid_'.$cat_ID );
			}
		
			If ($_POST[je_ai_setting_imageurl_.$cat_ID] != NULL)
			{
				If ($_POST[je_ai_setting_imageurl_.$cat_ID] != get_option('je_ai_setting_imageurl_'.$cat_ID))
				{
					update_option( 'je_ai_setting_imageurl_'.$cat_ID, $_POST[je_ai_setting_imageurl_.$cat_ID] );
				}
			}
			else
			{
				delete_option( 'je_ai_setting_imageurl_'.$cat_ID );
			}
		} // End category images
		
		# Delete all old options of the teams
		$option_mailreciptient = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename WHERE option_name LIKE 'je_ai_setting_mailreciptient_%%'", $p_tablename), ARRAY_A);
		If (count($option_mailreciptient) != NULL)
		{
			$i = 0;
			foreach ($option_mailreciptient as $i => $value)
			{
				delete_option( $option_mailreciptient[$i]["option_name"] );
				$i++;
			}
		}
		
		# Add new options of the teams and the locations
		$i = 0;
		foreach ($_POST as $key => $value)
		{
			if (strpos($key, 'je_ai_setting_mailreciptient_') !== false)
			{
				If (($value != NULL) && ($value != ""))
				{
					update_option( "je_ai_setting_mailreciptient_" . $i, $value );
					$i++;
				}
			}
		}
	}
	
	wp_enqueue_media();

	$option_mailreciptient = $wpdb->get_results( $wpdb->prepare ( "SELECT option_name, option_value FROM $tablename WHERE option_name LIKE 'je_ai_setting_mailreciptient_%%'", $p_tablename), ARRAY_A);
	
	?>
		<form method="post" action="">
			<?php settings_fields( 'je_ai_options' ); ?>
			<h3>E-Mail Empfänger für die Zeitung</h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="je_ai_setting_header">E-Mail Empfänger</label></th>
					<td>
							<?php
							If (count($option_mailreciptient) != NULL) {
								foreach ($option_mailreciptient as $i => $value) {
									$x = 1;
									$option_mailreciptient_name = $option_mailreciptient[$i]["option_name"];
									$option_mailreciptient_value = $option_mailreciptient[$i]["option_value"];
									?>
									<div id="mailreciptient">
									<input type="text" size="25" id="<?php echo $option_mailreciptient_name; ?>" name="<?php echo $option_mailreciptient_name; ?>" value="<?php echo $option_mailreciptient_value ?>" />
									<button name="<?php echo $option_mailreciptient_name; ?>_button" onclick="je_ai_setting_mailreciptient_remove('mailreciptient'); return false;">Empfänger entfernen</button>
									<br>
									</div>
									<?php
									$x++;
								}
							}
							else
							{
								?>
										<input type="text" size="25" id="je_ai_setting_mailreciptient_0" name="je_ai_setting_mailreciptient_0" value="" /><br>
								<?php
							}
							?>		
							<br>
							<button name="myInputButton" onclick="this.disabled=true; je_ai_setting_mailreciptient(); return false;" id="myForm">Empfänger hinzufügen</button>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="je_ai_setting_header">E-Mail Kopfzeile</label></th>
					<td><textarea cols="50" rows="5" id="je_ai_setting_header" name="je_ai_setting_header"><?php echo get_option('je_ai_setting_header'); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="je_ai_setting_seperator">Trennzeichen</label></th>
					<td><input type="text" size="50" maxlength="50" id="je_ai_setting_seperator" name="je_ai_setting_seperator" value="<?php echo get_option('je_ai_setting_seperator'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="je_ai_setting_footer">E-Mail Fußzeile</label></th>
					<td><textarea cols="50" rows="5" id="je_ai_setting_footer" name="je_ai_setting_footer"><?php echo get_option('je_ai_setting_footer'); ?></textarea></td>
				</tr>
			</table>
			<h3>Kategorie Bilder</h3>
			
			<?php
			$categories = get_categories();
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="je_ai_setting_category_default">Standard</label></th>
					<td>
						<img src="<?php echo get_option('je_ai_setting_imageurl_default'); ?>" width="150" class="customaddmediaimg">
						<input type="hidden" id="je_ai_setting_imageid_default" name="je_ai_setting_imageid_default" value="<?php echo get_option('je_ai_setting_imageid_default'); ?>" size="10" class="customaddmediaid">
						<input type="hidden" id="je_ai_setting_imageurl_default" name="je_ai_setting_imageurl_default" value="<?php echo get_option('je_ai_setting_imageurl_default') ?>" size="75" class="customaddmediaurl">
						<a href="#" class="button customaddmedia">Bild auswählen</a>
						<a href="#" class="button customaddmediaclear">Löschen</a>
					</td>
				</tr>
				<?php
				foreach ($categories as $category)
				{
					$cat_name = $category->cat_name;
					$cat_ID = $category->cat_ID;
					
					?>
					<tr valign="top">
						<th scope="row"><label for="je_ai_setting_category_<?php echo $cat_name; ?>"><?php echo $cat_name; ?></label></th>
						<td>
							<img src="<?php echo get_option('je_ai_setting_imageurl_'.$cat_ID); ?>" width="150" class="customaddmediaimg">
							<input type="hidden" id="je_ai_setting_imageid_<?php echo $cat_ID; ?>" name="je_ai_setting_imageid_<?php echo $cat_ID; ?>" value="<?php echo get_option('je_ai_setting_imageid_'.$cat_ID); ?>" size="10" class="customaddmediaid">
							<input type="hidden" id="je_ai_setting_imageurl_<?php echo $cat_ID; ?>" name="je_ai_setting_imageurl_<?php echo $cat_ID; ?>" value="<?php echo get_option('je_ai_setting_imageurl_'.$cat_ID); ?>" size="75" class="customaddmediaurl">
							<a href="#" class="button customaddmedia">Bild auswählen</a>
							<a href="#" class="button customaddmediaclear">Löschen</a>
						</td>
					</tr>
					<?php
				}
				?>
			</table>	
			<?php submit_button(); ?>
		</form>
<?php
}

# *************************************
# The formular for the users to send journals
# *************************************
function je_ai_sc_formular()
{
	?>
	<script type="text/javascript" src="<?php echo plugins_url ('/js/je-article-import-formular.js', __FILE__) ?>"></script>
	<?php
	if ($_SERVER['REQUEST_METHOD'] === "POST")
	{	
		global $wpdb;
		$tablename = $wpdb->prefix . "je_ai";
		$wpdb->je_ai = $wpdb->prefix . "je_ai";
				
		$values = array(
			'date' => time(),
			'author' => $_POST["str_author"],
			'header' => $_POST["str_header"],
			'body' => $_POST["str_body"],
			'category' => $_POST["int_category"],
		);

		$formats_values = array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
		);
		
		If ($wpdb->insert($wpdb->je_ai, $values, $formats_values) == FALSE)
		{
			die('Error: ' . mysql_error($con));
		}
		
		?>
		<span style="color:green">Bericht wurde versendet!</span><br>
		<p><a href="">Weiteren Bericht einreichen</a></p>
		
		<?php
	}
	else
	{
	?>
	<form action="" method="post" name="frm_article">
		<dl>
			<h3>Betreff:</h3>
			<input type="text" id="a" name="str_header" size="100" maxlength="50" value="" class="input_header"/><br>
			<p>Verbleibende Zeichen: <span class="input_header_left">50</span></p>
			
			<h3>Autor:</h3>
			<input type="text" id="b" name="str_author" size="100" maxlength="50" value="" class="input_author" /><br>
			<p>Verbleibende Zeichen: <span class="input_author_left">50</span></p>
			
			<h3>Kategorie auswählen:</h3>
			<?php
			$categories = get_categories();
			?>
			<select name="int_category">
				<?
				foreach ($categories as $category)
				{
					?>
					<option value="<?php echo $category->cat_ID; ?>"><?php echo $category->cat_name; ?></option>
					<?php
				}
				
				?>
			</select>
			
			<h3>Bericht:</h3>
			<textarea id="c" name="str_body" cols="100" rows="15" maxlength="1800" class="input_body"></textarea>
			<p>Verbleibende Zeichen: <span class="input_body_left">1800</span></p>
		</dl>
		<p>
		<class="submit"><input type="submit" name="submit_journal" value="Senden" class="submit_send" />
		<class="submit"><input type="reset" name="reset" value="Zurücksetzen" class="submit_reset" />
		</p>
	</form>
	<?php
	}
}

# *************************************
# Register and define the settings
# *************************************
function je_ai_admin_init()
{
	add_option ( 'je_ai_setting_mailreciptient_0', '');
	add_option ( 'je_ai_setting_header', '');
	add_option ( 'je_ai_setting_seperator', '');
	add_option ( 'je_ai_setting_footer', '');
	register_setting ('je_ai_options', 'je_ai_setting_mailreciptient_0');
	register_setting ('je_ai_options', 'je_ai_setting_header');
	register_setting ('je_ai_options', 'je_ai_setting_seperator');
	register_setting ('je_ai_options', 'je_ai_setting_footer');
}

# *************************************
# The global actions
# *************************************
register_activation_hook( __FILE__, 'je_ai_install' );
add_action( 'admin_init', 'je_ai_admin_init' );
add_action ('admin_menu', 'je_create_main_menu');
add_action ('admin_menu', 'je_ai_create_menu');
add_shortcode ('je_ai_sc_formular', 'je_ai_sc_formular');
add_action( 'admin_bar_menu', 'je_ai_admin_bar', 999 );

# *************************************
# Update 
# *************************************
If (get_option('je_ai_db_version' ) != $je_ai_db_version)
{
	If (get_option('je_ai_db_version' ) == "0.1")
	{
		$mailreciptients = get_option('je_ai_setting_mailreciptient');
		$mailreciptient = explode(";", $mailreciptients);
		
		$x = 0;
		foreach ($mailreciptient as $reciptient)
		{
			update_option( 'je_ai_setting_mailreciptient_'.$x, $reciptient);
			$x++;
		}
		delete_option( 'je_ai_setting_mailreciptient' );
	}
	update_option( 'je_ai_db_version', $je_ai_db_version );
}

?>