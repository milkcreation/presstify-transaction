<?php 
namespace tiFy\Core\Templates\Admin\Model\AjaxExport;

class AjaxExport extends \tiFy\App\Factory
{
	/* = ARGUMENTS = */
	// Classe de la vue
	protected $View		= null;
		
	/** == == **/
	public function display()
	{
	?>	
	<form id="tiFy_View_Admin_AjaxExport" method="get" action="">
        <div>	
        	<h4><?php _e( 'Paramètres d\'export', 'tify' );?></h4>
		</div>
       	
       	<div> 		
			<h4><?php _e( 'Limitation de l\'export', 'tify' );?></h4>
						
			<label >
				<span style="width:180px; display:inline-block;"><?php _e( 'A partir de l\'enregistrement', 'tify');?></span>&nbsp;
				<input type="number" class="export-options" name="export[from]" value="<?php echo $this->Export['from'];?>" size="4" />
			</label>
			<br />
			<label>
				<span style="width:180px; display:inline-block;"><?php _e( 'Nombre d\'enregistrements', 'tify');?></span>&nbsp;
				<input type="number" class="export-options" name="export[to]" value="<?php echo $this->_pagination_args['total_items'];?>" size="4" />
			</label>
		</div>
		
		<div>
			<h4><?php _e( 'Numéroter les lignes', 'tify' );?></h4>
		
			<label>
				<input type="radio" class="export-options" name="export[show_num]" value="1" <?php checked( (int) $this->Export['show_num'] === 1 );?>>&nbsp;
				<?php _e( 'Oui', 'tify' );?>
			</label>&nbsp;&nbsp;&nbsp;
			<label>
				<input type="radio" class="export-options" name="export[show_num]" value="0" <?php checked( (int) $this->Export['show_num'] === 0 );?>>&nbsp;
				<?php _e( 'Non', 'tify' );?>
			</label>&nbsp;&nbsp;&nbsp;
		</div>				
		
		<div class="datas">
			<h4><?php _e( 'Données d\'export', 'tify' );?></h4>
			<ul>				
			<?php foreach( (array) $this->get_columns() as $cname => $cheader ) :?>
				<li>
					<label>
						<input type="checkbox" name="export[col][<?php echo $cname;?>]" checked="checked" />
						&nbsp;<?php echo $cheader;?>
					</label>
				</li>
			<?php endforeach;?>
			</ul>
		</div>
		<button class="submit-action" type="submit"><?php _e( 'Lancer l\'export', 'tify' );?></button>
	</form>  
	<?php
	}
	
	/* = AFFICHAGE = */
	/** == Rendu de la page  == **/
	public function render()
	{
	?>
	<div class="wrap">
		<h2>
			<?php echo $this->label( 'export_items' );?>
		</h3>
     	<?php $this->display();?>	
	</div>
	<?php
	}
}