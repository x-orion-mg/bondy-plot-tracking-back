<?php 

namespace App\Utils;


use App\Utils\EntityType;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('file_exists', [$this, 'fileExists']),
            new TwigFunction('entity_type', [$this, 'getEntityType']),
            new TwigFunction('year_range', [$this, 'getYearRange']),
        ];
    }

    public function fileExists(string $path): bool
    {
        return $this->filesystem->exists($path);
    }

    public function getEntityType(string $constant)
    {
        if (defined(Entitytype::class . '::' . $constant)) {
            return constant(Entitytype::class . '::' . $constant);
        }

        throw new \InvalidArgumentException("La constante $constant n'existe pas dans PathName.");
    }

    public function getYearRange(): array
    {
        $currentYear = (int) date('Y');
        return range($currentYear, 1900);
    }
}
