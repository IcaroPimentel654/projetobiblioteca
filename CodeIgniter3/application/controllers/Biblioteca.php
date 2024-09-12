<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Biblioteca extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Professor_model');
	}


	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function hello()
	{
		$this->load->view('inicio');
	}

	public function alunos()
	{
		$this->load->view('alunos');
	}

	public function buscarProfessores()
	{
		$professores = $this->Professor_model->get_professores();

		echo json_encode($professores);
		exit;
	}

	public function salvarProfessor()
	{
		$nome = $this->input->post('name');

		$retorno = $this->Professor_model->salvar_professor($nome);

		echo json_encode($retorno);
		exit;
	}

	public function editarProfessor()
	{
		$novoName = $this->input->post('novoName');
		$id = $this->input->post('id');

		$retorno = $this->Professor_model->editar_professor($novoName, $id);

		echo json_encode($retorno);
		exit;
	}

	public function deletarProfessor()
	{
		$id = $this->input->post('id');

		$retorno = $this->Professor_model->deletar_professor($id);

		echo json_encode($retorno);
		exit;
	}
}
