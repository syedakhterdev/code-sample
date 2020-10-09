<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_templates extends TO_Controller {

    protected $mosaico_config = array();
	//Constructor
    public function __construct()
    {
        parent::__construct();
		//$this->output->enable_profiler(TRUE);
		if ( $this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1 ) 
		{
			$this->load->database();
	
			//LIBS
			$this->load->library('ion_auth');
			$this->load->library('grocery_CRUD');
			$this->load->library('form_validation');
			$this->config->load('mosaico_config');
			$this->load->library('email');
			
			//Models
			$this->load->model('User_model');
			$this->load->model('Common_model');
			$this->load->model('Company_model');		
			$this->load->model('Rsm_form_model');
			
			//MISC
			$this->data['rsm'] = $this->rsm;
			$this->data['controller'] = "email_templates";
			$this->rsm['th_url']=$this->data['rsm']['th_url']=$this->rsm['themes_url']."/lite";
			$this->data['menu_c'][1][1]=54;
			$this->mosaico_config = $this->config->item('mosaico_backend');
		}
	}

    public function index()
	{
		if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
		    $data['templates'] = json_encode($this->db->get("email_templates")->result_array(), JSON_PRETTY_PRINT);
			$data['title']="Mosaico Email Templates";
			$data['descr']="";

			$this->load->mosaico(FALSE, $data);
			
			//$this->_example_output($output);
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function forms()
	{
		if ($this->ion_auth->logged_in())
		{
			if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1) {
	
				$crud = new grocery_CRUD();
	 
				$crud->set_table('forms_email_templates');
				$crud->set_relation('form', 'rsm_form', 'name_en');
				$crud->set_relation('template', 'email_templates', 'template_name');
				
				
				$crud->required_fields('form', 'template');

        		// only admin can reset user password
				$this->data['gcrud'] = $crud->render();
				
				
				$this->data['title']="Email Templates <-> Forms Mappings";
				$this->data['descr']="";
				$this->data['tpl_part']=$this->data['controller']."_forms";
				$this->_new_output($this->data);
			}
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function representatives()
	{
		if ($this->ion_auth->logged_in())
		{
			if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->form_mailer == 1) {
	
				$crud = new grocery_CRUD();
	 
				$crud->set_table('representatives');
				$crud->set_relation('representative_company', 'companies', 'company_name');
                $crud->set_rules('representative_name', 'Please Enter a Valid Representative Name (upto 256 Characters)!', 'required|max_length[255]');
				$crud->set_rules('representative_email', 'Please Enter a Valid Representative Email!', 'required|valid_email|max_length[255]');
				$crud->required_fields('representative_name', 'representative_email', 'representative_company');

        		// only admin can reset user password
				$this->data['gcrud'] = $crud->render();
				
				
				$this->data['title']="Company Representatives";
				$this->data['descr']="";
				$this->data['tpl_part']=$this->data['controller']."_forms";
				$this->_new_output($this->data);
			}
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function editor()
	{
		if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
		    //$this->load->library('../core/security');
		    if($this->input->get("template")){
		        //check if provided template url is absolute or relative 
		        $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
                            (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
                            (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
                            (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";
		        $data['template'] = (((bool) preg_match($pattern, $this->input->get("template"))) ? '' : base_url() . 'mosaico/') . $this->input->get("template");
		    }
		    
		    if($this->input->get("name")){
		        $data['name'] = $this->input->get("name");
		    }
		    
			$data['title']="Mosaico Email Template Editor";
			$data['descr']="";
			$data["csrf_token"] = $this->security->get_csrf_token_name();
            $data["csrf_hash"] = $this->security->get_csrf_hash();
			$this->load->mosaico(TRUE, $data);
			
			//$this->_example_output($output);
		}
		else {
			redirect('auth/login', 'refresh');
		}
	}
	
	public function save_template(){
	    if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1 && $this->input->method(TRUE) == "POST")
		{
    	    $hash = $this->input->post( 'hash' );
            $meta = $this->input->post( 'metadata' );
            $name = $this->input->post( 'name' );
            $content = $this->input->post( 'content' );
            $html = $this->input->post( 'html' );
            
            $data = array();
            
            if( isset( $html ) && $html !== '' ){
                $data['template_html'] = $html;
            }
            
            if( isset( $name ) && $name !== '' ){
                $data['template_name'] = $name;
            }

            if( isset( $meta ) && $meta !== '' ){
                $data['template_metadata'] = $meta;
            }
            
            if( isset( $content ) && $content !== '' ){
                $data['template_content'] = $content;
            }
            $data['template_hash'] = $hash;
            $this->db->where("template_hash", $hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $this->db->set($data);
                $this->db->where("template_hash", $hash);
                $this->db->update('email_templates');
            } else{
                $data['template_hash'] = $hash;
                $this->db->insert('email_templates', $data);
            }
            $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template saved successfully." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function get_template($template_hash){
	    if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1 && $this->input->method(TRUE) == "GET")
		{
            $this->db->where("template_hash", $template_hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $result = $this->db->select('template_content, template_metadata')->get_where('email_templates', array("template_hash" => $template_hash))->row();
                $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		    }
            else{
                $this->output->set_status_header(404)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template with the key / hash " . $template_hash . " not found." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            }
            
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function delete_template($template_hash){
	    if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1 && $this->input->method(TRUE) == "GET")
		{
            $this->db->where("template_hash", $template_hash);
            $this->db->from('email_templates');
            if($this->db->count_all_results() == 1){
                $result = $this->db->delete('email_templates', array("template_hash" => $template_hash));
                $this->output->set_status_header(200)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template deleted successfully!" ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		    }
            else{
                $this->output->set_status_header(404)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Template with the key / hash " . $template_hash . " not found." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            }
            
		}    
        else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
	}
	
	public function ProcessUploadRequest()
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{

        	$files = array();
        
        	if ( $this->input->method(TRUE) == "GET" )
        	{
        
        		$dir = scandir( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] );
        		foreach ( $dir as $file_name )	
        		{
        			$file_path = $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name;
        			if ( is_file( $file_path ) )
        			{
        				$size = filesize( $file_path );
        				
        				$file = [
        					"name" => $file_name,
        					"url" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] . $file_name,
        					"size" => $size
        				];
        
        
        				if ( file_exists( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] . $file_name ) )
        				{
        					$file[ "thumbnailUrl" ] = $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'THUMBNAILS_URL' ] . $file_name;
        				} 
        
        				$files[] = $file;
        			}
        		}
        	}
        	else if ( !empty( $_FILES ) )
        	{
        		foreach ( $_FILES[ "files" ][ "error" ] as $key => $error )
        		{
        			if ( $error == UPLOAD_ERR_OK )
        			{
        				$tmp_name = $_FILES[ "files" ][ "tmp_name" ][ $key ];
        
        				$file_name = $_FILES[ "files" ][ "name" ][ $key ];
        				
        				$file_path = $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name;
        
        				if ( move_uploaded_file( $tmp_name, $file_path ) === TRUE )
        				{
        					$size = filesize( $file_path );
        
        					$image = new Imagick( $file_path );
        
        					$image->resizeImage( $this->mosaico_config[ 'THUMBNAIL_WIDTH' ], $this->mosaico_config[ 'THUMBNAIL_HEIGHT' ], Imagick::FILTER_LANCZOS, 1.0, TRUE );
        					$image->writeImage( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'THUMBNAILS_DIR' ] . $file_name );
        					$image->destroy();
        					
        					$file = array(
        						"name" => $file_name,
        						"url" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] . $file_name,
        						"size" => $size,
        						"thumbnailUrl" => $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'THUMBNAILS_URL' ] . $file_name
        					);
        
        					$files[] = $file;
        				}
        				else
        				{
        					$this->output->set_status_header(400)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Error Uploading File(s). Make sure that the required directories exist on the server." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				    exit;
        				}
        			}
        			else
        			{
        				$this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Error Uploading File(s)." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				exit;
        			}
        		}
        	}
        	
        	$this->output->set_header( "Content-Type: application/json; charset=utf-8" );
        	$this->output->set_header( "Connection: close" );
        
        	$this->output->set_content_type('application/json')->set_output( json_encode( array( "files" => $files ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
    
    /**
     * handler for img requests
     */
    public function ProcessImgRequest()
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
        	if ( $this->input->method(TRUE) == "GET" )
        	{
        		$method = $this->input->get( "method" );
        
        		$params = explode( ",", $this->input->get( "params" ) );
        
        		$width = (int) $params[ 0 ];
        		$height = (int) $params[ 1 ];
        
        		if ( $method == "placeholder" )
        		{
        			$image = new Imagick();
        
        			$image->newImage( $width, $height, "#707070" );
        			$image->setImageFormat( "png" );
        
        			$x = 0;
        			$y = 0;
        			$size = 40;
        
        			$draw = new ImagickDraw();
        
        			while ( $y < $height )
        			{
        				$draw->setFillColor( "#808080" );
        
        				$points = [
        					[ "x" => $x, "y" => $y ],
        					[ "x" => $x + $size, "y" => $y ],
        					[ "x" => $x + $size * 2, "y" => $y + $size ],
        					[ "x" => $x + $size * 2, "y" => $y + $size * 2 ]
        				];
        
        				$draw->polygon( $points );
        
        				$points = [
        					[ "x" => $x, "y" => $y + $size ],
        					[ "x" => $x + $size, "y" => $y + $size * 2 ],
        					[ "x" => $x, "y" => $y + $size * 2 ]
        				];
        
        				$draw->polygon( $points );
        
        				$x += $size * 2;
        
        				if ( $x > $width )
        				{
        					$x = 0;
        					$y += $size * 2;
        				}
        			}
        
        			$draw->setFillColor( "#B0B0B0" );
        			$draw->setFontSize( $width / 5 );
        			$draw->setFontWeight( 800 );
        			$draw->setGravity( Imagick::GRAVITY_CENTER );
        			$draw->annotation( 0, 0, $width . " x " . $height );
        
        			$image->drawImage( $draw );
        
        			$this->output->set_header( "Content-type: image/png" );
        
        			echo $image;
        		}
        		else
        		{
        			$file_name = $this->input->get( "src" );
        
        			$path_parts = pathinfo( $file_name );
        
        			switch ( $path_parts[ "extension" ] )
        			{
        				case "png":
        					$mime_type = "image/png";
        					break;
        
        				case "gif":
        					$mime_type = "image/gif";
        					break;
        
        				default:
        					$mime_type = "image/jpeg";
        					break;
        			}
        
        			$file_name = $path_parts[ "basename" ];
        
        			$image = $this->ResizeImage( $file_name, $method, $width, $height );
        
        			$this->output->set_header( "Content-type: " . $mime_type );
        
        			echo $image;
        		}
        	}
		}
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }

    /**
     * handler for dl requests
     */
    public function ProcessDlRequest()
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
        	$html = $this->InsertImages($this->input->post(  "html" ));
        
        	/* perform the requested action */
        
        	switch ( $this->input->post( "action" ) )
        	{
        		case "download":
        		{
        			$this->load->helper('download');
                    force_download($this->input->post( "filename" ), $html);
                    
        			break;
        		}
        
        		case "email":
        		{
        			$to = $this->input->post( "rcpt" );
        			$subject = $this->input->post( "subject" );
        			
        			
        			
        			if ( !$this->email->valid_email( $to ) )
        			{
        				$this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please provide a valid email." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
        				exit;
        			}
        			$this->config->load('email', TRUE);
        			$email_config = $this->config->item("email_config", "email");
        			$this->email->initialize($email_config);
        			$this->email->to($to);
                    $this->email->from($this->ion_auth->user()->row()->username . '@' . parse_url( base_url(), PHP_URL_HOST ),'CRM');
                    $this->email->set_newline("\r\n");
                    $this->email->subject($subject);
                    $this->email->message($html);
                    if($this->email->send())
                    {
                        $this->email->start_process();
                    }
                    else
                    {
                        $this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => show_error($this->email->print_debugger()) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
                    }
        			break;
        		}
        	}
        }
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }

    /**
     * handler for Merging the specified form into the Selected Email Template and sending it to Customers.
     */
    public function form_mailer()
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->form_mailer == 1)
		{
		    $this->load->model('Rsm_form_model');
        	$company = $this->input->post(  "company" );
        	$template = $this->input->post(  "template" );
            $form = $this->input->post(  "form" );
            $subject = $this->input->post(  "subject" );
            $form_name = $this->db->get_where('rsm_form', array('form_id' => $form));
            $template_html = $this->db->get_where('email_templates', array('template_hash' => $template))->row()->template_html;
            $html = $this->InsertImages($template_html);
            
    	    $emails = $this->db->select('representative_email')->get('representatives')->result_array();
			$form_data = $this->Rsm_form_model->get_form_data($form, $company);
			$to = array();
			foreach($emails as $email){
    			if ( !$this->email->valid_email( $email['representative_email'] ) )
    			{
    				$this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please provide a valid email." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
    				exit;
    			}
    			array_push( $to, $email['representative_email']);
			}
			$message = $this->MergeForm($form_name, $form_data, $html);
			$this->config->load('email', TRUE);
			$email_config = $this->config->item("email_config", "email");
			$this->email->initialize($email_config);
			$this->email->to($to);
            $this->email->from($this->ion_auth->user()->row()->username . '@' . parse_url( base_url(), PHP_URL_HOST ),'CRM');
            $this->email->set_newline("\r\n");
            $this->email->subject($subject);
            $this->email->message($message);
            if($this->email->send())
            {
                $this->email->start_process();
            }
            else
            {
                $this->output->set_status_header(500)->set_content_type('application/json')->set_output( json_encode( array( "msg" => show_error($this->email->print_debugger()) ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display();
			}
        }
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
    
    protected function MergeForm($form_name, $form_data, $html)
    {
       $variable_syntax = '/{\K[^}]*(?=})/m';
       $variables = array();
       preg_match_all($variable_syntax, $html, $variables);
       foreach($variables[0] as $variable){
           $var = $variable;
           $variable = explode( "$", trim($variable) )[1];
           $form_field = explode( ".", $variable );
           $form = $form_field[0];
           $field = $form_field[1];
           $field_value = $this->fetch_field_value($form_data, $field);
           $html = str_replace( "{" . $var . "}", $field_value, $html);
       }
       return $html;
    }
    
    protected function fetch_field_value($form_data, $field_name){
        foreach($form_data as $field)
            if($field_name == $field['name'])
                return $field['data'];
    }
    
    protected function InsertImages($html)
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
        	/* create static versions of resized images */
        	$matches = [];
        
        	$num_full_pattern_matches = preg_match_all( '#<img.*?src=".*(img[^"]*)#i', $html, $matches); 
        
        
            for ( $i = 0; $i < $num_full_pattern_matches; $i++ ) 
        	{
        
        		if ( stripos( $matches[ 1 ][ $i ], "img/?src=" ) !== FALSE )
        		{
        
        		    $src_matches = [];
        
        
        			if ( preg_match( '#.*src=(.*)&amp;method=(.*)&amp;params=(.*)#i', $matches[ 1 ][ $i ], $src_matches ) !== FALSE )
        			{
        
        
        				$file_name = urldecode( $src_matches[ 1 ] );
        
        
        
        				$file_name = substr( $file_name, strlen( $this->mosaico_config[ 'BASE_URL' ] . $this->mosaico_config[ 'UPLOADS_URL' ] ) );
        
        				$method = urldecode( $src_matches[ 2 ] );
        
        				$params = urldecode( $src_matches[ 3 ] );
        				$params = explode( ",", $params );
        				$width = (int) $params[ 0 ];
        				$height = (int) $params[ 1 ];
        
        
        
        
        				if ( $width == 0 || $height == 0 )
        				{
        					    $image = new Imagick( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name );
        					    $image_geometry = $image->getImageGeometry();
        					    $image_ratio =  (double) $image_geometry[ "width" ] / $image_geometry[ "height" ];
        					    if ( $width == 0 ) {
        					        $width =  $height * $image_ratio;
         					        $width = (int) $width;
        					    } else {
        					        $height = $width / $image_ratio;
        						    $height = (int) $height;
        					    }
        	    		}
        
        
        				$static_file_name = $method . "_" . $width . "x" . $height . "_" . $file_name;
        
        				
        				$html = str_ireplace(  $this->mosaico_config[ 'BASE_URL'] . $matches[ 1 ][ $i ], $this->mosaico_config[ 'SERVE_URL'] . $this->mosaico_config[ 'STATIC_URL' ] . ( $static_file_name ), $html );
        
        				$image = $this->ResizeImage( $file_name, $method, $width, $height );
        				$image->writeImage( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'STATIC_DIR' ] . $static_file_name );
        			}
        
        		}
        
        
        	}
        	
        	return $html;
        
        }
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }
    
    public function send_pending_emails()
    {
        $this->email->send_queue();
	}
	
	
    /**
     * function to resize images using resize or cover methods
     */
    
    function ResizeImage( $file_name, $method, $width, $height )
    {
        if ($this->ion_auth->logged_in() && (int)$this->ion_auth->user()->row()->email_template_manager == 1)
		{
        	$image = new Imagick( $this->mosaico_config[ 'BASE_DIR' ] . $this->mosaico_config[ 'UPLOADS_DIR' ] . $file_name );
        
        	if ( $method == "resize" )
        	{
        	    $image->resizeImage( $width, $height, Imagick::FILTER_LANCZOS, 1.0 );
        	}
        	else // $method == "cover"
        	{
        		$image_geometry = $image->getImageGeometry();
        
        		$width_ratio = $image_geometry[ "width" ] / $width;
        		$height_ratio = $image_geometry[ "height" ] / $height;
        
        		$resize_width = $width;
        		$resize_height = $height;
        
        		if ( $width_ratio > $height_ratio )
        		{
        			$resize_width = 0;
        		}
        		else
        		{
        			$resize_height = 0;
        		}
        
        		$image->resizeImage( $resize_width, $resize_height, Imagick::FILTER_LANCZOS, 1.0 );
        
        		$image_geometry = $image->getImageGeometry();
        
        		$x = ( $image_geometry[ "width" ] - $width ) / 2;
        		$y = ( $image_geometry[ "height" ] - $height ) / 2;
        
        		$image->cropImage( $width, $height, $x, $y );
        	}
        	
        	return $image;
		}
    	else {
			$this->output->set_status_header(403)->set_content_type('application/json')->set_output( json_encode( array( "msg" => "Please login to continue." ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
		}
    }

	
}
?>