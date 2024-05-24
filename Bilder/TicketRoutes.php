<?php

namespace Lupcom\IsotopeQrCodeBundle\Controller;

class TicketRoutes
{
    private $security;
    private $templateArgs = [];

    public function __construct(Security $security) {
        $this->security = $security;
    }

    #[Route('/einloesen', name: "einloesen", defaults: ["_scope" => "frontend"])]
    public function einloesen(Request $request): Response
    {
        if ($this->isAuthenticated()) {
            return $this->accessGranted($request);
        }

        return $this->accessDenied($request);
    }

    #[Route('/einloesen/scanner', name: "einloesen_scanner", defaults: ["_scope" => "frontend"])]
    public function scanner(Request $request) {
        if ($this->isAuthenticated()) {
            return $this->generateResponse("fe_tool_scanner");
        }
        
        return $this->accessDenied($request);
    }

    #[Route('/einloesen/suche', name: "einloesen_suche", defaults: ["_scope" => "frontend"])]
    public function suche(Request $request) {
        if ($this->isAuthenticated()) {
            return $this->generateResponse("fe_tool_suche");
        }
        
        return $this->accessDenied($request);
    }

    private function accessDenied(Request $request): Response {
        $container = \Contao\System::getContainer();
        $legacyRouting = true;
        $urlSuffix = "";
        $loginPage = PageModel::findByIdOrAlias("login", ["having" => "published='1'"]);

        if ($container->hasParameter("contao.legacy_routing")) {
            $legacyRouting = $container->getParameter('contao.legacy_routing');
        }

        if ($legacyRouting) {
            $urlSuffix = \Contao\Config::get("urlSuffix");
        }else if (!empty($loginPage)) {
            $urlSuffix = $loginPage->urlSuffix;
        }
        
        if (!empty($loginPage)) {
            return new RedirectResponse("/" . $loginPage->alias . $urlSuffix);
        }

        return $this->generateResponse("fe_access_denied");
    }

    private function isAuthenticated(): bool {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addTemplateArgument("username", $this->security->getUser()->username);
            return true;
        }

        return false;
    }

    private function generateResponse($template, $args = []) {
        return new Response(Helper::generateTemplate($template, [...$args, ...$this->templateArgs]));
    }

    private function addTemplateArgument($key, $value) {
        $this->templateArgs = [...$this->templateArgs, $key => $value];
    }
}