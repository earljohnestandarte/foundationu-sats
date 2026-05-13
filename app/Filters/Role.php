<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Role implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('user_role');

        if (empty($userRole) || empty($arguments)) {
            return redirect()->to(site_url('/'))->with('error', 'You do not have permission to access that page.');
        }

        if (! in_array($userRole, $arguments)) {
            return redirect()->to(site_url('/'))->with('error', 'You do not have permission to access that page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
