<?php  
class ControllerModulePdfcart extends Controller { 
	public function index() {
		$this->response->setOutput($this->htmlTemplate());
	}
	
	private function htmlTemplate() {
		/* SHOP INFORMATION */
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = HTTPS_IMAGE;
		} else {
			$server = HTTP_IMAGE;
		}	

		$this->data['name'] = $this->config->get('config_name');
				
		if ($this->config->get('config_logo') && file_exists(DIR_IMAGE . $this->config->get('config_logo'))) {
			$this->data['logo'] = $server . $this->config->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}
		$this->data['telephone'] = $this->config->get('config_telephone');
		$this->data['address'] = $this->config->get('config_address');
		$this->data['email'] = $this->config->get('config_email');
		
		/* PRODUCTS INFORMATION */
		$this->language->load('checkout/cart');
		
		if ($this->config->get('config_cart_weight')) {
			$this->data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
		} else {
			$this->data['weight'] = '';
		}

		$this->load->model('tool/image');
		
		$this->data['products'] = array();
		
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}			

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['option_value'];	
				} else {
					$filename = $this->encryption->decrypt($option['option_value']);
					
					$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
				}
				
				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
			}
			
			// Display prices
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$price = false;
			}
			
			// Display prices
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
			} else {
				$total = false;
			}
			
			$this->data['products'][] = array(
				'thumb'    => $image,
				'name'     => $product['name'],
				'model'    => $product['model'],
				'option'   => $option_data,
				'quantity' => $product['quantity'],
				'stock'    => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'reward'   => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
				'price'    => $price,
				'total'    => $total,
				'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		// Gift Voucher
		$this->data['vouchers'] = array();
		
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$this->data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount']),
					'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)   
				);
			}
		}
		
		// Totals
		$this->load->model('setting/extension');
		
		$total_data = array();					
		$total = 0;
		$taxes = $this->cart->getTaxes();
		
		// Display prices
		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
				
				$sort_order = array(); 
			  
				foreach ($total_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
	
				array_multisort($sort_order, SORT_ASC, $total_data);			
			}
		}
		
		$this->data['totals'] = $total_data;
	    $this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/pdfcart.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/pdfcart.tpl';
		} else {
			$this->template = 'default/template/module/pdfcart.tpl';
		}

		return $this->render();
  	}
	
	public function pdf() {
		$html = $this->htmlTemplate();
		
		include_once DIR_SYSTEM . 'library/dompdf/dompdf_config.inc.php';
		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		
		$dompdf->set_paper('a4', "portrait");
		$dompdf->render();
		
		$pdfContent = $dompdf->output();
		
		$filename = "Корзина покупок - " . $this->data['name'] . ".pdf";
		$filename = urlencode($filename);
		$filename = str_replace('+', '%20', $filename);
		
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename*=UTF-8''" . $filename); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");            
		header("Content-Length: " . strlen($pdfContent));
		
		echo $pdfContent;
	}
}
?>