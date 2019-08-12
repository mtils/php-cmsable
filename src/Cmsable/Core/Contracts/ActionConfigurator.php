<?php
/**
 *  * Created by mtils on 01.12.18 at 10:13.
 **/

namespace Cmsable\Core\Contracts;


interface ActionConfigurator
{
    /**
     * If you assigned your ActionConfigurator to a route that points to a
     * controller action you can modify the __construct dependencies here.
     * $dependencies will be an array with parameter names=>values. To allow
     * the complete replacement of parameters the returned array will be used.
     *
     * @param array $dependencies
     *
     * @return array The modified dependencies
     */
    public function modifyConstructorDependencies(array $dependencies);

    /**
     * Sets the "action". This is mostly [$controllerInstance, $method] but can
     * also be a Closure or some other callable.
     *
     * @param callable $action
     *
     * @return self
     */
    public function setAction(callable $action);

    /**
     * Set the matched page
     *
     * @param Page $page
     *
     * @return self
     */
    public function setPage(Page $page);

    /**
     * Before calling the $action (see setAction()) the action parameters will be
     * built. And between building parameters and calling the action this method
     * here will be called. Return the processed parameters.
     *
     * @see self::modifyConstructorDependencies()
     *
     * @param array $parameters
     *
     * @return array
     */
    public function modifyActionParameters(array $parameters);
}