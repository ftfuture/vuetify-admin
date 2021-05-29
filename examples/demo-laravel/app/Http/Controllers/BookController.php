<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBook;
use App\Http\Requests\UpdateBook;
use App\Http\Resources\Book as BookResource;
use App\Http\Resources\BookCollection;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ftfuture\LaravelAdmin\Filters\SearchFilter;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Book::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return BookCollection
     */
    public function index()
    {
        return new BookCollection(
            QueryBuilder::for(Book::class)
                ->allowedFields(['id', 'isbn', 'title'])
                ->allowedFilters([
                    AllowedFilter::custom('q', new SearchFilter(['isbn', 'title', 'description'])),
                    AllowedFilter::exact('id'),
                    AllowedFilter::exact('publisher', 'publisher_id'),
                    AllowedFilter::callback('authors', function (Builder $query, $value) {
                        $query->whereHas('authors', function (Builder $query) use ($value) {
                            $query->whereIn('id', is_array($value) ? $value : [$value]);
                        });
                    }),
                    AllowedFilter::exact('commentable'),
                    'title',
                    AllowedFilter::exact('category'),
                    AllowedFilter::exact('formats'),
                    AllowedFilter::scope('pricer_than'),
                    AllowedFilter::scope('cheaper_than'),
                    AllowedFilter::scope('published_before'),
                    AllowedFilter::scope('published_after'),
                ])
                ->allowedSorts(['id', 'isbn', 'title', 'price', 'publication_date'])
                ->allowedIncludes(['category', 'publisher', 'authors', 'reviews', 'media'])
                ->paginate()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Book  $book
     * @return BookResource
     */
    public function show(Book $book)
    {
        return new BookResource($book->load(['category', 'publisher', 'authors', 'reviews', 'media']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return BookResource
     */
    public function store(StoreBook $request)
    {
        $book = Book::create($request->all());
        $book->authors()->sync($request->input('author_ids'));

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBook $request
     * @param \App\Models\Book $book
     * @return BookResource
     */
    public function update(UpdateBook $request, Book $book)
    {
        $book->update($request->all());

        if ($request->has('author_ids')) {
            $book->authors()->sync($request->input('author_ids'));
        }

        if ($id = $request->input('add_author_id')) {
            $book->authors()->attach($id);
        }

        if ($id = $request->input('remove_author_id')) {
            $book->authors()->detach($id);
        }

        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Book $book
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
