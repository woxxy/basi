<table class="table table-bordered table-striped">
	<thead>
		<th>Nome fornitore</th>
		<th>Partita IVA</th>
		<th></th>
	</thead>
	<tbody>
		<?php foreach($fornitori as $f) : ?>
		<tr>
			<td><?php echo $f['nome'] ?></td>
			<td><?php echo $f['partita_iva'] ?></td>
			<td><a class="btn btn-success" href="<?php echo site_url('clienti_per_fornitore?partita_iva='.$f['partita_iva'])?>">Mostra</a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>