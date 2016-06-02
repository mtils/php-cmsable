<?php


namespace Cmsable\Contacts\Providers;
use Illuminate\Support\ServiceProvider;
use Ems\App\Http\Forms\Fields\ImageDbField;
use FormObject\Form;
use Collection\NestedArray;

class ContactsServiceProvider extends ServiceProvider
{

    protected $routeGroup = [
        'namespace' =>  'Cmsable\Contacts\Http\Controllers'
    ];

    protected $contacts = [];

    protected $profiles = [];

    protected $avatarUrls = [];

    public function boot()
    {

        view()->composer('users.detail-layout', function ($view) {

            if (!isset($view['model'])) {
                return;
            }

            $user = $view['model'];

            if (!$contact = $this->getUserContact($user)) {
                return;
            }

            $view['userTitle'] = $contact->summary;

            if (!$profile = $this->getUserProfile($user)) {
                return;
            }

            if ($profile->profession) {
                $view['userType'] = $profile->profession;
            }

            if (!$userAvatar = $this->userAvatarImage($user)) {
                return;
            }

            $view['userAvatar'] = $userAvatar;

        });

        view()->composer(['partials.sidebar','partials.main-header'], function ($view) {
            if ($image = $this->userAvatarImage($this->app['auth']->user())) {
                $view['currentUserAvatar'] = $image;
            }
        });

    }

    public function register()
    {
        $events = $this->app['events'];

        $events->listen('form.fields-setted.user-form', function($fields) {

            $this->extendUserForm($fields);
            return;
            $companyForm = $this->app->make('App\Http\Forms\CompanyForm');

            $companyFields = $companyForm->getFields();

            $fields('main')->push(Form::text('company__extern_id', 'Kundennummer'))->after('email');

            $categories = UserSearchForm::categories();
            unset($categories['']);

            $categorySelect = Form::selectOne('company__category_id', 'Kategorie')
                                    ->setSrc($categories);

            $fields('main')->push($categorySelect)->after('company__extern_id');

            foreach ($companyFields as $fieldList) {
                $fields->push($fieldList->copy('company'));
            }

            $fields('company__companyfields')->setTitle('Firmendaten');

        });

        $events->listen('resource::users.query', function($query){

            $query->leftJoinOn('contact');
            $query->leftJoinOn('contact.address');
            $query->leftJoinOn('contact.profile');

        });

        $events->listen('resource::users.found', function($user){

            // trigger loading of contact to assure admin page values
            if ($user->contact) {
                $user->contact->profile;
            }

        });

        $events->listen('form.model-setted.user-form', function($form, $model){

            if(!$contact = $model->contact) {
                return;
            }

            $form->fillByArray($contact->toArray(), 'contact');

            if ($address = $contact->address) {
                $form->fillByArray($address->toArray(), 'contact__address');
            }

            if ($profile = $contact->profile) {
                $form->fillByArray($profile->toArray(), 'contact__profile');
            }

        });

        $events->listen('resource::users.validation-rules.setted', function(&$rules){
            $this->extendUserValidationRules($rules);
        });

        $events->listen('resource::users.updating', function($model, $attributes) {

            if (!isset($attributes['contact'])) {
                return;
            }

            $contactData = NestedArray::withoutNested($attributes['contact'], '.');
            $addressData = NestedArray::get($attributes['contact'], 'address');
            $profileData = NestedArray::get($attributes['contact'], 'profile');

            $contact = $model->contact()->updateOrCreate([],$contactData);

            $contact->address()->updateOrCreate(
                [],
                $addressData
            );

            foreach (['image_id', 'preview_image_id', 'avatar_id'] as $key) {
                if (isset($profileData[$key]) && !$profileData[$key]) {
                    unset($profileData[$key]);
                }
            }

            $contact->profile()->updateOrCreate(
                [],
                $profileData
            );

        });

    }

    protected function extendUserForm($fields)
    {
        $mainContactFields = Form::fieldList('main-contact-data');
        $mainContactFields->push(
            ImageDbField::create('contact__profile__preview_image_id', 'Portrait'),
            Form::text('contact__salutation', 'Anrede'),
            Form::text('contact__title', 'Titel'),
            Form::text('contact__forename', 'Vorname'),
            Form::text('contact__surname', 'Nachname')
        )->addCssClass('horizontal-split');
        $fields('main')->push($mainContactFields)->after('email');
        $fields('main')->push(Form::text('contact__company','Firma'))->after('main-contact-data');

        $contactFields = Form::fieldList('contact-data');
        $contactFields->push(
            Form::text('contact__email', 'Email Privat'),
            Form::text('contact__email2', 'Email Geschäftlich'),
            Form::text('contact__phone', 'Telefon Privat'),
            Form::text('contact__phone_business', 'Telefon Geschäftlich'),
            Form::text('contact__mobile', 'Handy'),
            Form::text('contact__fax', 'Fax'),
            Form::text('contact__website', 'Website')
        )->setTitle('Kontaktmöglichkeiten');
        $fields->push($contactFields)->after('main');

        $addressFields = Form::fieldList('address');

        $locationFields = Form::fieldList('location-group')->addCssClass('horizontal-split');

        $locationFields->push(
            Form::text('contact__address__street', 'Straße'),
            Form::text('contact__address__house_number', 'Hausnummer'),
            Form::text('contact__address__postcode', 'Postleitzahl'),
            Form::text('contact__address__location', 'Ort')
        );

        $addressFields->push(
            $locationFields,
            Form::text('contact__address__addition', 'Adresszusatz')
        )->setTitle('Adresse');
        $fields->push($addressFields)->after('contact-data');

        $contactFields = Form::fieldList('profile-data');

        $imagesFields = Form::fieldList('image-group')->addCssClass('horizontal-split');

        $imagesFields->push(
            ImageDbField::create('contact__profile__avatar_id', 'Avatar'),
            ImageDbField::create('contact__profile__image_id', 'Großes Bild')
        );

        $contactFields->push(
            $imagesFields,
            Form::html('contact__profile__profile', 'Profil'),
            Form::text('contact__profile__teaser', 'Motto'),
            Form::text('contact__profile__profession', 'Beruf'),
            Form::text('contact__profile__qualification', 'Qualifikation')->setMultiline(true),
            Form::text('contact__profile__specialization', 'Spezialisierung'),
            Form::text('contact__profile__cite', 'Zitat')->setMultiline(true)
        )->setTitle('Profil');
        $fields->push($contactFields)->after('address');
    }

    protected function extendUserValidationRules(&$rules)
    {
        $contactRules = [
            'salutation'        => 'min:3|max:32',
            'title'             => 'min:2|max:64',
            'forename'          => 'min:1|max:64',
            'surname'           => 'min:1|max:64',
            'email'             => 'email|max:128',
            'email2'            => 'email|max:128',
            'phone'             => 'min:5|max:24',
            'phone_business'    => 'min:5|max:24',
            'mobile'            => 'min:5|max:24',
            'fax'               => 'min:5|max:24',
            'website'           => 'url|min:5|max:64',
            'company'           => 'min:2|max:128'
        ];

        $addressRules = [
            'iso_country' => 'min:3|max:3',
            'street' => 'min:1|max:255',
            'house_number' => 'min:1|max:16',
            'postcode' => 'min:4|max:12',
            'location' => 'min:1|max:255'
        ];

        $profileRules = [
            'profile' => 'min:12',
            'teaser' => 'min:3|max:255',
            'profession' => 'min:2|max:255',
            'qualification' => 'min:2',
            'specialization' => 'min:2|max:255',
            'cite' => 'min:3'
        ];

        foreach ($contactRules as $key=>$rule) {
            $rules["contact.$key"] = $rule;
        }

        foreach ($addressRules as $key=>$rule) {
            $rules["contact.address.$key"] = $rule;
        }

        foreach ($profileRules as $key=>$rule) {
            $rules["contact.profile.$key"] = $rule;
        }

    }

    protected function getUserContact($user)
    {
        if (isset($this->contacts[$user->id])) {
            return $this->contacts[$user->id];
        }

        if (!$contact = $user->contact) {
            $this->contacts[$user->id] = false;
            return false;
        }

        $this->contacts[$user->id] = $contact;

        return $contact;
    }

    protected function getUserProfile($user)
    {
        if (isset($this->profiles[$user->id])) {
            return $this->profiles[$user->id];
        }

        if (!$contact = $this->getUserContact($user)) {
            $this->profiles[$user->id] = false;
            return false;
        }

        if (!$profile = $contact->profile) {
            $this->profiles[$user->id] = false;
            return false;
        }

        $this->profiles[$user->id] = $profile;

        return $profile;
    }

    protected function userAvatarImage($user)
    {

        if (isset($this->avatarUrls[$user->id])) {
            return $this->avatarUrls[$user->id];
        }

        if (!$profile = $this->getUserProfile($user)) {
            $this->avatarUrls[$user->id] = false;
            return false;
        }


        if (!$profile->avatar_id) {
            $this->avatarUrls[$user->id] = false;
            return false;
        }

        if (!$image = $this->app['FileDB\Model\FileDBModelInterface']->getById($profile->avatar_id)) {
            $this->avatarUrls[$user->id] = false;
            return false;
        }

        $this->avatarUrls[$user->id] = $image->url;

        return $this->avatarUrls[$user->id];
    }
}