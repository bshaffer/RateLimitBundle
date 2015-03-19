<?php

/*
 * This file is part of the Perimeter package.
 *
 * (c) Adobe Systems, Inc. <bshafs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perimeter\RateLimitBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    const MAX_RESULTS = 20;

    protected $templating;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function indexAction(Request $request)
    {
        return new Response($this->templating->render('PerimeterRateLimitBundle:Dashboard:index.html.php'));
    }

    public function dataAction(Request $request)
    {
        $throttler = $this->container->get('perimeter.rate_limit.throttler');
        $storage   = $this->container->get('perimeter.rate_limit.storage.admin');
        $meters = array();

        $requestRate = $throttler->getTokenRate();
        $maxRequestRate = max(100, $requestRate + 1);

        foreach($throttler->getTopMeters() as $meterId => $tokens) {
            $meter = $storage->getMeter($meterId);
            $meter['tokens'] = $tokens;

            $meter['class'] = $tokens > $meter['limit_threshold'] ?
                'alert' : ($tokens > $meter['warn_threshold'] ? 'warn' : '');

            $meters[$meterId] = $meter;
        }

        return new Response($this->templating->render('PerimeterRateLimitBundle:Dashboard:data.html.php', compact(
            "requestRate",
            "maxRequestRate",
            "meters"
        )));
    }
}
