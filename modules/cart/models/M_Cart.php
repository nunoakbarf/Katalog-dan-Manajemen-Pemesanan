<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Cart extends CI_Model {

	public function get_data(){
		$sql = "SELECT prod_id, prod_name, qty, price, (price * qty) as total_harga FROM cart order by prod_id";
		return $this->db->query($sql);
	}
	function fetch_data($query){
		$this->db->select("prod_id, prod_name, qty, price, (price * qty) as total_harga");
		$this->db->from("cart");
		if($query != ''){
			$this->db->like('prod_name', $query);
			$this->db->or_like('qty', $query);
			$this->db->or_like('price', $query);
		}
		return $this->db->get();
	}
	public function jumlah_cart(){
		$this->db->select_sum('qty','jumlah');
		$this->db->from('cart');
		return $this->db->get('')->row();
	}
	public function hapus_cart($where, $table){
		$this->db->where($where);
		$this->db->delete($table);
	}
	public function hapus_cart_transaction($where, $table){
		$this->db->where($where);
		$this->db->delete($table);
	}
	public function hapus_all_cart(){
		$this->db->empty_table('cart');
	}

	public function find($id){
		$result = $this->db->where('prod_id', $id)
						   ->limit(1)
						   ->get('products');
		if($result->num_rows() > 0){
			return $result->row();
		}else{
			return array();
		}
	}
	function input_data($data,$table){
		$this->db->insert($table,$data);
	}

	






	public function id_user(){
		$username = $this->session->userdata('username');
		$row = $this->db->query('select id_user from users where username ="'.$username.'"');
		$user = $row->row();
        return $id_user = $user->id_user;
	}
	public function co(){
        $id_user = $this->id_user();

		$row = $this->db->query('select max(order_num) as order_num from orders');
		$order_num = $row->row();
        $nomor = $order_num->order_num;
        $no_order = 1;
        if($nomor == 0){
            $no_orderf= $no_order;
        } else {
            $no_orderf= $nomor+1;
        }

        $query = $this->db->query('select * from cart where id_user = "'.$id_user.'"')->result_array();
        $tgl=date("Y-m-d H:i:s");
        $data = array(
            'order_num'		=> $no_orderf,
            'order_date' 	=> $tgl,
            'id_user' 		=> $id_user
        );
        $this->db->insert('orders', $data);

        foreach ($query as $q ) {
			$query = $this->db->query('select * from cart where id_user = "'.$id_user.'"')->result_array();
            $prod_id 		= $q['prod_id'];
            $prod_price 	= $q['price'];
            $prod_name 		= $q['prod_name'];
            $data_d = array(
                'order_num' 	=> $no_orderf,
                'prod_name'		=> $prod_name,
                'prod_id' 		=> $prod_id,
                'prod_price'	=> $prod_price,
				'quantity' 		=> $q['qty'],
				'id_user' 		=> $id_user
            );
            $this->db->insert('orderitems', $data_d);
        }
        $this->db->where('id_user', $id_user);
        $this->db->delete('cart');
		$this->load->view('account/order_successv');
    }
	
}