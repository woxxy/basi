<table class="table table-striped table-bordered">
	<thead>
		<th>Nome Scuola</th>
		<th>Circoscrizione</th>
		<th>Indirizzo</th>
	</thead>
	<tbody>
		<?php foreach ($scuole as $scuola) : ?>
		<tr>
			<td><?php echo $scuola['nome'] ?></td>
			<td><?php echo $scuola['circoscrizione'] ?></td>
			<td><?php echo $scuola['indirizzo'] ?></td>
			<td><a href="<?php echo site_url('menu_del_giorno?nome_scuola='.urlencode($scuola['nome']).'&circoscrizione_scuola='.urlencode($scuola['circoscrizione'])) ?>" class="btn btn-success">Mostra</a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>