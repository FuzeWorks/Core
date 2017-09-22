<style class="tracy-debug">



</style>

<div class="fuzeworks-LoggerPanel">
<h1> Logger</h1>

<div class="tracy-inner">
	<table>
	<thead>
	<tr>
		<th>#</th>
		<th>Type</th>
		<th>Message</th>
		<th>File</th>
		<th>Line</th>
		<th>Timing</th>
	</tr>
	</thead>

	<tbody>
	<?php foreach ($logs as $key => $log): ?>
		<?php if ($log['type'] === 'LEVEL_STOP')
		{
			continue;
		}
		elseif ($log['type'] === 'LEVEL_START')
		{
			$log['type'] = 'CINFO';
		}
		?>
	<tr class="<?php echo($log['type']); ?>">
		<td><?php echo(  htmlspecialchars($key)); ?></td>
		<td><?php echo(  htmlspecialchars ($log['type'])); ?></td>
		<td><?php echo(  htmlspecialchars ($log['message'])); ?></td>
		<td><?php echo( empty($log['logFile']) ? 'x' : htmlspecialchars ($log['logFile'])); ?></td>
		<td><?php echo( empty($log['logLine']) ? 'x' : htmlspecialchars ($log['logLine'])); ?></td>
		<td><?php echo(round($log['runtime'] * 1000, 4)); ?> ms</td>
	</tr>
	<?php endforeach ?>
	</tbody>
	</table>
</div>
</div>
