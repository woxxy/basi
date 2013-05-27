<p>Piatti offerti più di frequente da "<?php echo $fornitore['nome'] ?>" con partita IVA <?php echo $fornitore['partita_iva']?></p>

<table class="table table-striped table-bordered">
	<thead>
		<th>Nome piatto</th>
		<th>Volte</th>
	</thead>
	<tbody>
		<?php foreach ($result as $r) : ?>
			<tr>
				<td><?php echo $r['nome'] ?></td>
				<td><?php echo $r['count'] ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>