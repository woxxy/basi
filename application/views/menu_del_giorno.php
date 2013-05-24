<p>Menu del giorno <strong><?php echo htmlentities($giorno) ?></strong> per la scuola <strong><?php echo htmlentities($scuola) ?></strong> della circoscrizione <strong>n.<?php echo htmlentities($circoscrizione) ?></strong></p>

<table class="table table-striped table-bordered">
	<thead>
		<th>Nome piatto</th>
		<th>Portata</th>
	</thead>
	<tbody>
		<?php
			$menu = array();
			foreach ($piatti as $piatto)
			{
				$menu[$piatto['tipo']][] = $piatto;
			}

			foreach (array('primo', 'secondo', 'contorno') as $portata):
				foreach ($menu[$portata] as $p) : ?>
			<tr>
				<td><?php echo $p['nome'] ?></td>
				<td><?php echo $p['tipo'] ?></td>
			</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
	</tbody>
</table>
<br>
<a href="<?php echo site_url('menu_del_giorno')?>" class="btn btn-success">Indietro</a>