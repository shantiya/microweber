<?php
namespace MicroweberPackages\Cms\tests;

class ContentManagerServiceProviderTest extends BaseTest
{
	public function testContentManagerWhenUsing() {
		$this->assertInstanceOf(\MicroweberPackages\Content\ContentManager\ContentManager::class, app('content_manager'));
	}
}