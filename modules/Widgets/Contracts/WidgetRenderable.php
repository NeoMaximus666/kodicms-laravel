<?php namespace KodiCMS\Widgets\Contracts;

interface WidgetRenderable extends Widget {

	/**
	 * @return string
	 */
	public function getFrontendTemplate();

	/**
	 * @return string
	 */
	public function getDefaultFrontendTemplate();
}