<?php

namespace Ellllllen\Http\Controllers;

use Illuminate\Http\Request;
use Ellllllen\PersonalWebsite\Articles\GetArticles;
use Ellllllen\PersonalWebsite\Articles\ManageArticles;
use Ellllllen\PersonalWebsite\Articles\Clicks\GetArticleClicks;
use Ellllllen\PersonalWebsite\Articles\Clicks\LogArticleClick;

class ArticleController extends Controller
{
    /**
     * @var GetArticles
     */
    private $getArticles;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ManageArticles
     */
    private $manageArticles;

    /**
     * ArticleController constructor.
     * @param GetArticles $getArticles
     * @param Request $request
     * @param ManageArticles $manageArticles
     */
    public function __construct(GetArticles $getArticles, Request $request, ManageArticles $manageArticles)
    {
        $this->getArticles = $getArticles;
        $this->request = $request;
        $this->manageArticles = $manageArticles;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('articles.index')->with('articles', $this->getArticles->paginate());
    }

    /**
     * @param int $articleID
     * @param LogArticleClick $logArticleClick
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(int $articleID, LogArticleClick $logArticleClick)
    {
        $article = $this->getArticles->findOrFail($articleID);

        $logArticleClick->storeLog($article, $this->request->ip());

        if ($article->hasSeparateController()) {
            return $article->loadSeparateController();
        }

        return view('articles.show', compact('article'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store()
    {
        $this->validate($this->request, [
            'title' => ['required', 'unique:articles,title'],
            'section' => 'required',
            'image' => ['required', 'image']
        ]);

        $this->manageArticles->store($this->request->all());

        return redirect()->route('home');
    }

    /**
     * @param int $articleID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $articleID)
    {
        $article = $this->getArticles->findOrFail($articleID);

        $this->manageArticles->destroy($article);

        return redirect()->route('articles.index');
    }

    /**
     * @param int $articleID
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(int $articleID)
    {
        $article = $this->getArticles->findOrFail($articleID);

        return view('articles.edit', compact('article'));
    }

    /**
     * @param int $articleID
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(int $articleID)
    {
        $article = $this->getArticles->findOrFail($articleID);

        $this->validate($this->request, [
            'title' => ['required', "unique:articles,title,{$articleID},id"],
            'section' => 'required',
            'image' => ['image']
        ]);

        $this->manageArticles->update($this->request->all(), $article);

        return redirect()->route('articles.show', ['id' => $article->id]);
    }

    /**
     * @param GetArticleClicks $getArticleClicks
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function report(GetArticleClicks $getArticleClicks)
    {
        $articleClicks = $getArticleClicks->paginate();

        return view('articles.report', compact('articleClicks'));
    }

    /**
     * @param GetArticleClicks $getArticleClicks
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClicks(GetArticleClicks $getArticleClicks)
    {
        $articleClicks = $getArticleClicks->getChartData();

        return response()->json($articleClicks);
    }
}
