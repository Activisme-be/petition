<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * User management controller.
 *
 * @author    Tim Joosten   <Topairy@gmail.com>
 * @copyright Activisme-BE  <info@activisme.be>
 * @license:  MIT license
 * @since     2017
 * @package   Petitions
 */
class Users extends MY_Controller
{
    public $user        = []; /** @var array $user         The user information for the authencated user.   */
    public $abilities   = []; /** @var array $abilities    The ablities for the authencated user.           */
    public $permissions = []; /** @var array $permissions  The permissions for the authencated user.        */

    /**
     * Users constructor.
     *
     * @return int|void|null
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'blade', 'form_validation', 'pagination', 'paginator']);
        $this->load->helper(['url']);

        $this->user        = $this->session->userdata('user');
        $this->abilities   = $this->session->userdata('abilities');
        $this->permissions = $this->session->userdata('permissions');
    }

    /**
     * Return the list of middlewares you want to be applied,
     * Here is list of some valid options
     *
     * admin_auth                    // As used below, simplest, will be applied to all
     * someother|except:index,list   // This will be only applied to posts()
     * yet_another_one|only:index    // This will be only applied to index()
     *
     * @return array
     */
    protected function middleware()
    {
        // TODO: Implement middleware. 

        return ['auth', 'admin'];
    }

    /**
     * Get the backend index for the login management.
     *
     * @see:url('GYET|HEAD', 'http://www.petities.activisme.be/users')
     * @return Blade view
     */
    public function index()
    {
        $data['title'] = 'Loginbeheer';
        $data['db_users'] = new Authencate;

        // Users pagination.
        $this->pagination->initialize($this->paginator->relation(base_url('users'), count($data['db_users']->all()), 3, 3));
        $data['users']      = $data['db_users']->skip($this->input->get('page'))->take(15)->get();
        $data['users_link'] = $this->pagination->create_links();

        return $this->blade->render('users/index', $data);
    }

    /**
     * Search for a user in the system.
     *
     * @see:url('GET|HEAD', 'http://www.petities.activisme.be/users/search')
     * @return Blade view
     */
    public function search()
    {
        // BUG: The request now is a post. This need to set to GET|HEAD in the form.

        $term = $this->security->xss_clean($this->input->get('term'));

        $data['title'] = 'Zoekresultaten voor' . $term;
        $data['users'] = Authencate::where('name', 'LIKE', '%' . $term . '%')
            ->orWhere('username', 'LIKE', '%' . $term . '%')
            ->orWhere('email', 'LIKE', '%' . $term . '%')
            ->get();

        $this->blade->render('users/index', $data);
    }

    /**
     * Return the data about the user. That user is given in the 3th uri segment.
     *
     * @see:url('GET|HEAD', 'http://www.petities.activisme.be/users/getUser/{userid}') 
     * @return JSON array
     */
    public function getUser()
    {
        // BUG: For now the json returns also the password. This is unsafe.
        //      Remove the password from the json array.

        $user = Authencate::find($this->security->xss_clean($this->uri->segment(3)));
		echo json_encode($user);
    }

    /**
     * Show a the specific user data for a account.
     *
     * @see:url('GET|HEAD, 'http://www.petities.activisme.be/users/show/{userId}')
     * @return Response|Blade view
     */
    public function show()
    {
        $userId = $this->security->xss_clean($this->uri->segment(3));

        $data['user']  = Authencate::find($userId);
        $data['title'] = 'Profiel: ' . $data['user']->name . '(' . $data['user']->username . ')';

        if ((int) count($data['user']) === 0) {
            $this->session->set_flashdata('class', 'alert alert-success');
            $this->session->set_flashdata('message', 'Wij konden geen gebruiker vinden met de id #' . $userId);

            return redirect($_SERVER['HTTP_REFERER']);
        }

        return $this->blade->render('users/show', $data);
    }

	/**
	 * Block a user in the system.
     *
     * @see:url('GET|HEAD', 'http://www.petities.activisme.be/users/block/{userId}')
     * @return Response|Redirect
	 */
    public function block()
	{
        // TODO: Implement the ban table into the phinx migrations.

        $this->form_validation->set_rules('id', 'Gebruikers ID', 'trim|required');
		$this->form_validation->set_rules('reason', 'Rede blokkering', 'trim|required');

		if ($this->form_validation->run() === false) { // Validation errors.
            $this->session->set_flashdata('class', 'alert alert-danger');
            $this->session->set_flashdata('message', 'Wij konden de blokkering niet verwerken.');

            return redirect(base_url('users')); // Validation error so move to the index page.
        }

        // No validation errors move on with our logic.
        $param['userid'] = $this->security->xss_clean($this->input->post('id'));

        $banData = [
            'reason'      => $this->security->xss_clean($this->input->post('reason')),
            'executed_by' => $this->user['id'],
        ];

        $db['user']    = Authencate::find($param['userid']);
        $db['reason']  = Ban::create($banData);
		$db['blocked'] = $db['user']->update(['blocked' => 'Y', 'ban_id' => $db['reason']->id]);

		if ($db['blocked'] && $db['reason']) { // The user is banned.
			$this->session->set_flashdata('class', 'alert alert-success');
			$this->session->set_flashdata('message', $db['user']->name. ' is geblokkeerd.');
		}

		return redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	 * Unblock a user in the system.
	 *
	 * @see:url('GET|HEAD', 'http://www.activisme.be/users/unblock/{userId}')
	 * @return Response|redirect
	 */
	public function unblock()
	{
		$param['userId'] = $this->security->xss_clean($this->uri->segment(3));
		$db['user']      = Authencate::find($param['userId']);

		if ($db['user']->update(['blocked' => 'N', 'ban_id' => 0])) { // User is unblocked in the system.
			$this->session->set_flashdata('class', 'alert alert-success');
			$this->session->set_flashdata('message', $db['user']->name . ' is gedeblokkeerd.');
		}

		return redirect($_SERVER['HTTP_REFERER']);
	}

    /**
     * Delete an user account on the system.
     *
     * @see:url('GET|HEAD', 'http://www.petities.activisme.be/users/delete/{userId}')
     * @return Redirect|Response
     */
    public function delete()
    { 
        $userId     = $this->uri->segment(3);
        $db['user'] = Authencate::find($this->security->xss_clean($userId));

        if ((int) $this->user['id'] === $db['user']->id || in_array('Admin', $this->permissions)) { // The user is the user ifself or an admin.
            if ($db['user']->delete()) { // The user has been deleted. 
                $class   = 'alert alert-success';
                $message = 'De gebruiker is verwijderd.';
            }
        } else { // The user is not an admin or the user itself.
            $class   = 'alert-danger';
            $message = 'U hebt geen machtiging om de gebruiker te verwijderen.';
        }

        $this->session->set_flashdata('class', $class); 
        $this->session->set_flashdata('message', $message);

        return redirect($_SERVER['HTTP_REFERER']);
    }
}
