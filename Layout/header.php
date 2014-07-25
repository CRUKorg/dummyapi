	<header>
		<?php if(!empty($this->messages)): ?>
		<div class="alerts">
			<?php foreach($this->messages as $message): ?>
			<div class="alert alert-<?php echo $message['type']; ?>">
				<?php echo $message['notice']; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</header>