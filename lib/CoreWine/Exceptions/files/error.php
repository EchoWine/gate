<html>
	<body style='background:#efefef'><div style='margin:30px auto;max-width:800px'>
		
		<div style='padding:20px;background:white;border-radius:15px;font-size:22px;border:1px solid #dedede'>

			<span style='font-size:21px; display:block'>
				<b><?php echo $class; ?></b>

				<?php if(!empty($e -> getFile())): ?>
					<?php echo $e -> getFile(); ?>
					in line <?php echo $e -> getLine();?>

				<?php endif; ?>
			</span>
			<span style='font-size:21px;'>
				<?php echo $e -> getMessage(); ?>
			</span>

		</div>
		<div style='margin-top:20px;padding:20px;background:white;border-radius:15px;font-size:22px;border:1px solid #dedede'>
			<div> StackTrace </div>
			<?php $i = 1; ?>
			<div style='font-size:14px'>
				<?php if(!empty($e -> getFile())): ?>
					<?php echo $e -> getFile(); ?>
					in line <?php echo $e -> getLine();?>
					<?php $i++; ?>
				<?php endif; ?>
			</div>

			<?php

			foreach($e -> getTrace() as $k):
				?>
				<div style='font-size:14px'>
					<?php echo $i;?>
					<?php if(isset($k['file'])): ?>
						<?php echo $k['file'];?> in line <?php echo $k['line'];?>
					<?php endif; ?>
				</div>

				<?php $i++; endforeach; ?>
			
	</div></div></div>
</body></html>