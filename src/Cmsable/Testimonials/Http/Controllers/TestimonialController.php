<?php 

namespace Cmsable\Testimonials\Http\Controllers;


use App\Http\Controllers\Controller;
use Ems\Contracts\Core\Repository;
use Ems\App\Helpers\ProvidesTexts;
use Cmsable\View\Contracts\Notifier;
use Cmsable\Http\Resource\SearchRequest;
use Cmsable\Http\Resource\CleanedRequest;


class TestimonialController extends Controller
{

    use ProvidesTexts;

    protected $repository;

    protected $notifier;

    public function __construct(Repository $repository, Notifier $notifier)
    {
        $this->repository = $repository;
        $this->notifier = $notifier;
    }

    public function show($id)
    {
        return view('testimonials.show')->withModel($this->repository->getOrFail($id));
    }

    public function index(SearchRequest $request)
    {

        $defaults = [
            'sort'  =>'created_at',
            'order' => 'desc'
        ];

        return view('testimonials.index')->withSearch($request->search($defaults));

    }

    public function create()
    {
        return view('testimonials.create')->withModel($this->repository->make());
    }

    public function store(CleanedRequest $request)
    {
        $testimonial = $this->repository->store($request->cleaned());
        $this->notifier->success($this->routeMessage('stored'));
        return redirect()->route('testimonials.index');
    }

    public function edit($id)
    {
        $user = $this->repository->getOrFail($id);
        return view('testimonials.edit')->withModel($id);
    }

    public function update(CleanedRequest $request, $id)
    {
        $user = $this->repository->getOrFail($id);
        $this->repository->update($user, $request->cleaned());
        $this->notifier->success($this->routeMessage('updated'));
        return redirect()->route('testimonials.edit',[$id]);
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