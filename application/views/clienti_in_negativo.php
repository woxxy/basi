<table class="table table-striped table-bordered">
	<thead>
		<th>Codice Fiscale</th>
		<th>Nome</th>
		<th>Cognome</th>
		<th>Telefono</th>
		<th>Pasti usufruiti</th>
		<th>Pasti pagati</th>
	</thead>
	<tbody>
		<?php foreach($result as $r) : ?>
			<tr>
				<td><?php echo strtoupper($r['cf'])?></td>
				<td><?php echo $r['nome']?></td>
				<td><?php echo $r['cognome']?></td>
				<td><?php echo $r['telefono']?></td>
				<td><?php echo $r['presenze']?></td>
				<td><?php echo $r['pasti']?></td>
			</tr>
		<?php endforeach;?>
	</tbody>
</table>