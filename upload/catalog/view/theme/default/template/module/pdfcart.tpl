<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">

<style>
body {
	padding-left: 20px;
	line-height: 2;
	font-size: 14px;
	font-family: Arial;
}

table.list {
	border-collapse: collapse;
	width: 100%;
}

table.list td, table.list th {
	border-right: 1px solid black;
	border-bottom: 1px solid black;
}

table.list tr td:first-child, table.list tr th:first-child {
	border-left: 1px solid black;
}

table.list tr:first-child td, table.list tr:first-child th {
	border-top: 1px solid black;
}

table.list td, table.list th {
	padding: 5px;
}

.right {
	text-align: right;
}

.cart-total table {
	width: 100%;
	margin-top: 10px;
}

#content {
	margin: 0 auto;
	margin-top: 50px;
}

#header-layout {
	width: 100%;
}

a {
	text-decoration: none;
}
</style>
</head>
<body>
<div id="header">
	<table id="header-layout">
		<tr>
			<td width="50%">
				<?php if ($logo) { ?>
				<img src="<?php echo $logo; ?>"/>
				<?php } ?>
			</td>
			<td rowspan="2" class="right">
				<?php echo $telephone; ?><br/>
				<?php echo $email; ?><br/>
				<?php echo $address; ?><br/>
			</td>
		</tr>
		<tr>
			<td style="text-aling: left; white-space: nowrap;">
				<?php echo $name; ?>
			</td>
		</tr>
</div>
<div id="content">
	<h1>Корзина покупок</h1>
	<table class="list">
	<thead>
		<tr>
			<th>Фото</th>
			<th>Наименование</th>
			<th>Модель</th>
			<th>Кол-во</th>
			<th>Цена</th>
			<th>Итого</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($products as $product): ?>
	<tr>
		<td><a href="<?php echo $product['href'] ?>"><img src="<?php echo $product['thumb']; ?>"/></a></td>
		<td><a href="<?php echo $product['href'] ?>"><?php echo $product['name']; ?></a>
			<?php if (!$product['stock']) { ?>
				<span class="stock">***</span>
			<?php } ?>
			<div>
				<?php foreach ($product['option'] as $option) { ?>
				- <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small><br />
				<?php } ?>
			</div>
			<?php if ($product['reward']) { ?>
			<small><?php echo $product['reward']; ?></small>
			<?php } ?>
		</td>
		<td><?php echo $product['model']; ?></td>
		<td><?php echo $product['quantity']; ?></td>
		<td><?php echo $product['price']; ?></td>
		<td><?php echo $product['total']; ?></td>
	</tr>
	<?php endforeach; ?>

	<?php foreach ($vouchers as $vouchers): ?>
	  <tr>
		<td class="image"></td>
		<td class="name"><?php echo $vouchers['description']; ?></td>
		<td class="model"></td>
		<td class="quantity"><input type="text" name="" value="1" size="1" disabled="disabled" />
		  &nbsp;<a href="<?php echo $vouchers['remove']; ?>"><img src="catalog/view/theme/default/image/remove.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" /></a></td>
		<td class="price"><?php echo $vouchers['amount']; ?></td>
		<td class="total"><?php echo $vouchers['amount']; ?></td>
	  </tr>
	<?php endforeach; ?>
	</tbody>
	</table>

	<div class="cart-total">
		<table id="total">
		  <?php foreach ($totals as $total) { ?>
		  <tr>
		    <td style="width: 100%;">&nbsp;</td>
			<td class="right"><b><?php echo $total['title']; ?>:</b></td>
			<td class="right"><?php echo $total['text']; ?></td>
		  </tr>
		  <?php } ?>
		</table>
	</div>
</div>
</body>
</html>