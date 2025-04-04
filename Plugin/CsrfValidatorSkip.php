<?php

namespace Lootly\Lootly\Plugin;

class CsrfValidatorSkip
{
    /**
     * Plugin around csrf validation
     *
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'lootly') {
            if ($request->getControllerName()=='create' && $request->getActionName()){
                return $proceed($request, $action);
            }
            return; // Skip CSRF check
        }
        return $proceed($request, $action); // Proceed Magento 2 core functionalities
    }
}
