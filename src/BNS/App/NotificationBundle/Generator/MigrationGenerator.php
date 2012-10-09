<?php

namespace BNS\App\NotificationBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;

use BNS\App\CoreBundle\Date\ExtendedDateTime;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@pixel-cookers.com>
 */
class MigrationGenerator extends Generator
{
	private $filesystem;
    private $skeletonDir;

	/**
	 * @param Filesystem $filesystem
	 * @param string	 $skeletonDir 
	 */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }
	
	/**
	 * @param string $rootDir
	 * @param string $bundleName
	 * @param string $notificationUniqueName
	 * @param bool	 $isCorrection
	 * @param string $disabledEngines
	 */
	public function generate($rootDir, $bundleName, $notificationUniqueName, $isCorrection, $disabledEngines)
	{
		$fullTime = new ExtendedDateTime();
		$fullTime->setTimestamp(time());
		
		$time = $fullTime->getTimestamp();
		$filePath = $rootDir . '/propel/migrations/' . 'PropelMigration_' . $time . '.php';
		$isCorrectionInteger = $isCorrection ? 1 : 0;
		$disabledEnginesString = null != $disabledEngines ? "'" . $disabledEngines . "'" : 'null';
		
		$parameters = array(
			'bundleName'				=> $bundleName,
			'notificationUniqueName'	=> $notificationUniqueName,
			'isCorrection'				=> $isCorrectionInteger,
			'disabledEngines'			=> $disabledEnginesString,
			'time'						=> $time,
			'fullTime'					=> $fullTime
		);
		
		// Finally, creating migration file
		$this->renderFile($this->skeletonDir, 'PropelMigration.php', $filePath, $parameters);
	}
}