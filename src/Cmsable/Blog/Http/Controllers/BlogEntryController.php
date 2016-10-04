<?php 

namespace Cmsable\Blog\Http\Controllers;


use App\Http\Controllers\Controller;
use Ems\Contracts\Core\Repository;
use Ems\App\Helpers\ProvidesTexts;
use Cmsable\Blog\Contracts\BlogEntryRepository;
use Cmsable\View\Contracts\Notifier;
use Cmsable\Http\Resource\SearchRequest;
use Cmsable\Foundation\Http\CleanedRequest;
use Permit\CurrentUser\ContainerInterface as Auth;


class BlogEntryController extends Controller
{

    use ProvidesTexts;

    protected $repository;

    protected $notifier;

    protected $auth;

    protected $searchDefaults = [
        'sort'  =>'created_at',
        'order' => 'desc'
    ];

    public function __construct(BlogEntryRepository $repository, Notifier $notifier, Auth $auth)
    {
        $this->repository = $repository;
        $this->notifier = $notifier;
        $this->auth = $auth;
        $this->middleware('auth', [
            'except' => ['index', 'show']
        ]);
    }

    public function index(SearchRequest $request)
    {

        $search = $request->search($this->searchDefaults);

        return view('blog-entries.index')->withSearch($search);

    }

    public function byYear(SearchRequest $request, $year)
    {

        $search = $request->search($this->searchDefaults);
        $search->where('year', $year);

        $vars = [
            'search' => $search,
            'year'   => $year
        ];

        return view('blog-entries.index')->with($vars);

    }

    public function byMonth(SearchRequest $request, $year, $month)
    {

        $search = $request->search($this->searchDefaults);
        $search->where('year', $year)->where('month', $month);

        $vars = [
            'search' => $search,
            'year'   => $year,
            'month'  => $month
        ];

        return view('blog-entries.index')->with($vars);

    }

    public function show($id)
    {
        $entry = is_numeric($id) ? $this->repository->getOrFail($id)
                                 : $this->repository->getByUrlSegmentOrFail($id);

        return view('blog-entries.show')->withModel($entry);
    }

    public function create()
    {
        return view('blog-entries.create')->withModel($this->repository->make());
    }

    public function store(CleanedRequest $request)
    {
        $blogEntry = $this->repository->store($request->cleaned());
        $this->notifier->success($this->routeMessage('stored'));
        return redirect()->route('blog-entries.index');
    }

    public function edit($id)
    {
        return view('blog-entries.edit')->withModel($this->repository->getOrFail($id));
    }

    public function update(CleanedRequest $request, $id)
    {
        $blogEntry = $this->repository->getOrFail($id);
        $this->repository->update($blogEntry, $request->cleaned());
        $this->notifier->success($this->routeMessage('updated'));
        return redirect()->route('blog-entries.edit',[$id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

        $this->repository->delete($this->repository->getOrFail($id));

        $this->notifier->success($this->routeMessage('destroyed'));

        return 'OK';

    }

}
