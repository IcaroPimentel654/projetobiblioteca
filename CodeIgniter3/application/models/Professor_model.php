<?php

class Professor_model extends CI_Model
{

    function __construct()
    {
        $this->load->database();
        $this->db->query("SET NAMES 'utf8'");
    }

    public function get_professores()
    {
        //SELECT * FROM professores;
        $this->db->select('*');
        $this->db->from('professores');
        //return $this->db->get()->row_array();  esse primeiro ele retorna a primeira linha buscada

        //return $this->db->get()->result_array();  esse segundo ele retorna todas as linhas buscadas 

        //return $this->db->get()->row_array();

        return $this->db->get()->result_array();
    }

    public function salvar_professor($nome)
    {
        $data = array(
            'nome' => $nome
        );

        return $this->db->insert('professores', $data);
    }

    public function editar_professor($novoName, $id)
    {
        $data = array(
            'nome' => $novoName
        );

        $this->db->where('id', $id);
        return $this->db->update('professores', $data);
    }

    public function deletar_professor($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('professores');
    }
}
