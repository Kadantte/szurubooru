<?php
class TagController
{
	public function listView($filter = 'order:alpha,asc', $page = 1)
	{
		$ret = Api::run(
			new ListTagsJob(),
			[
				JobArgs::ARG_PAGE_NUMBER => $page,
				JobArgs::ARG_QUERY => $filter,
			]);

		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';
		$context->highestUsage = TagSearchService::getMostUsedTag()->getPostCount();
		$context->filter = $filter;
		$context->transport->tags = $ret->entities;
		$context->transport->paginator = $ret;
	}

	public function autoCompleteView()
	{
		$filter = InputHelper::get('search');
		$filter .= ' order:popularity,desc';

		$job = new ListTagsJob();
		$job->getPager()->setPageSize(15);
		$ret = Api::run(
			$job,
			[
				JobArgs::ARG_QUERY => $filter,
				JobArgs::ARG_PAGE_NUMBER => 1,
			]);

		$context = Core::getContext();
		$context->transport->tags =
			array_values(array_map(
				function($tag)
				{
					return [
						'name' => $tag->getName(),
						'count' => $tag->getPostCount(),
					];
				}, $ret->entities));
	}

	public function relatedView()
	{
		$otherTags = (array) InputHelper::get('context');
		$tag = InputHelper::get('tag');

		$ret = Api::run(
			(new ListRelatedTagsJob),
			[
				JobArgs::ARG_TAG_NAME => $tag,
				JobArgs::ARG_TAG_NAMES => $otherTags,
				JobArgs::ARG_PAGE_NUMBER => 1
			]);

		$context = Core::getContext();
		$context->transport->tags =
			array_values(array_map(
				function($tag)
				{
					return [
						'name' => $tag->getName(),
						'count' => $tag->getPostCount(),
					];
				}, $ret->entities));
	}

	public function mergeView()
	{
		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';
	}

	public function mergeAction()
	{
		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';
		$context->handleExceptions = true;

		Api::run(
			new MergeTagsJob(),
			[
				JobArgs::ARG_SOURCE_TAG_NAME => InputHelper::get('source-tag'),
				JobArgs::ARG_TARGET_TAG_NAME => InputHelper::get('target-tag'),
			]);

		Messenger::message('Tags merged successfully.');
	}

	public function renameView()
	{
		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';
	}

	public function renameAction()
	{
		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';
		$context->handleExceptions = true;

		Api::run(
			new RenameTagsJob(),
			[
				JobArgs::ARG_SOURCE_TAG_NAME => InputHelper::get('source-tag'),
				JobArgs::ARG_TARGET_TAG_NAME => InputHelper::get('target-tag'),
			]);

		Messenger::message('Tag renamed successfully.');
	}

	public function massTagRedirectView()
	{
		$context = Core::getContext();
		$context->viewName = 'tag-list-wrapper';

		Access::assert(new Privilege(Privilege::MassTag));
	}

	public function massTagRedirectAction()
	{
		$this->massTagRedirectView();
		$suppliedOldPage = intval(InputHelper::get('old-page'));
		$suppliedOldQuery = InputHelper::get('old-query');
		$suppliedQuery = InputHelper::get('query');
		$suppliedTag = InputHelper::get('tag');

		$params = [
			'source' => 'mass-tag',
			'query' => $suppliedQuery ?: ' ',
			'additionalInfo' => $suppliedTag ? $suppliedTag : '',
		];
		if ($suppliedOldPage != 0 and $suppliedOldQuery == $suppliedQuery)
			$params['page'] = $suppliedOldPage;
		\Chibi\Util\Url::forward(\Chibi\Router::linkTo(['PostController', 'listView'], $params));
		exit;
	}
}
