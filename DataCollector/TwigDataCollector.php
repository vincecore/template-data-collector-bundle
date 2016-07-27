<?php

namespace Vincecore\TemplateDataCollectorBundle\DataCollector;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

class TwigDataCollector extends DataCollector implements LateDataCollectorInterface
{
    /**
     * @var FilesystemLoader
     */
    private $filesystemLoader;

    /**
     * @var \Twig_Profiler_Profile
     */
    private $profile;

    public function __construct(\Twig_Profiler_Profile $profile, FilesystemLoader $filesystemLoader)
    {
        $this->filesystemLoader = $filesystemLoader;
        $this->profile = $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $this->data['templates'] = $this->getUsedTemplates($this->profile);
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->data['templates'];
    }

    private function getUsedTemplates(\Twig_Profiler_Profile $profile)
    {
        $templates = array();

        foreach ($profile as $p) {
            if ($p->isTemplate()) {
                try {
                    $templates[] = array(
                        'template' => $p->getTemplate(),
                        'fullPath' => $this->filesystemLoader->getCacheKey($p->getTemplate()),
                    );
                } catch (\Exception $e) {
                }
            }

            $templates = array_merge($templates, $this->getUsedTemplates($p));
        }

        return $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig_clickable';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }
}
