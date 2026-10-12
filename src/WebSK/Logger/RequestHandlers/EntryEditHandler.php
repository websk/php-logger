<?php

namespace WebSK\Logger\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WebSK\Entity\InterfaceEntity;
use WebSK\Entity\InterfaceEntityService;
use WebSK\Logger\CompareHTML;
use WebSK\Logger\Entry\LoggerEntry;
use WebSK\Logger\LoggerConfig;
use WebSK\Utils\Sanitize;
use WebSK\Views\LayoutDTO;
use WebSK\Slim\RequestHandlers\BaseHandler;
use WebSK\Views\BreadcrumbItemDTO;
use WebSK\Logger\LoggerServiceProvider;
use WebSK\Views\PhpRender;

/**
 * Class EntryEditHandler
 * @package WebSK\Logger\RequestHandlers
 */
class EntryEditHandler extends BaseHandler
{

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param int $entry_id
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, int $entry_id): ResponseInterface
    {
        $entry_obj = LoggerServiceProvider::getEntryService($this->container)->getById($entry_id, false);
        if (!$entry_obj) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $html = '';
        $html .= $this->renderRecordHead($entry_id);

        try {
            $html .= $this->delta($entry_id);
            $html .= $this->renderObjectFields($entry_id);
        } catch (\Throwable $e) {
            $html .= '<div class="alert alert-danger">Структура объекта была изменена. Показ содержимого объекта невозможен.</div>';
        }

        $layout_dto = new LayoutDTO();
        $layout_dto->setTitle(date('Y.d.m H:i', $entry_obj->getCreatedAtTs()));
        $layout_dto->setContentHtml($html);
        $breadcrumbs_arr = [
            new BreadcrumbItemDTO(
                'Журналы',
                $this->urlFor(EntriesListHandler::class)
            ),
            new BreadcrumbItemDTO(
                $entry_obj->getObjectFullId(),
                $this->urlFor(
                    ObjectEntriesListHandler::class,
                    ['object_full_id' => urlencode($entry_obj->getObjectFullId())]
                )
            ),
        ];
        $layout_dto->setBreadcrumbsDtoArr($breadcrumbs_arr);

        return PhpRender::renderLayout($response, LoggerConfig::getAdminLayout(), $layout_dto);
    }

    /**
     * @param int $current_record_id
     * @return string
     */
    public function delta(int $current_record_id): string
    {
        $html = '';

        $current_record_obj = $this->getRequiredEntry($current_record_id);

        $prev_record_id = LoggerServiceProvider::getEntryService($this->container)
            ->getPrevRecordEntryId($current_record_id);

        if (!$prev_record_id) {
            return '<div>Предыдущая запись истории для этого объекта не найдена.</div>';
        }

        $prev_record_obj = $this->getRequiredEntry($prev_record_id);


        $edit_url = $this->urlFor(EntryEditHandler::class, ['entry_id' => (string)$prev_record_id]);

        // определение дельты

        $html .= '<h2>Изменения относительно <a href="' . $edit_url . '">предыдущей версии</a></h2>';

        $current_obj = $this->unserializeLoggedValue($current_record_obj->getSerializedObject());
        $prev_obj = $this->unserializeLoggedValue($prev_record_obj->getSerializedObject());

        $current_record_as_list = $this->convertValueToList($current_obj);
        ksort($current_record_as_list); // сортируем для красоты
        $prev_record_as_list = $this->convertValueToList($prev_obj);
        ksort($prev_record_as_list); // сортируем для красоты

        $added_rows = array_diff_key($current_record_as_list, $prev_record_as_list);
        $deleted_rows = array_diff_key($prev_record_as_list, $current_record_as_list);

        if ($added_rows || $deleted_rows) {
            $html .= '<table class="table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Поле</th>';
            $html .= '<th>Старое значение</th>';
            $html .= '<th>Новое значение</th>';
            $html .= '</tr>';
            $html .= '</thead>';

            foreach ($added_rows as $k => $v) {
                $html .= '<tr>';
                $html .= '<td><b>' . $k . '</b></td>';
                $html .= '<td style="background-color: #eee;"></td>';
                $html .= '<td>' . Sanitize::sanitizeTagContent($this->renderDeltaValue($v)) . '</td>';
                $html .= '</tr>';
            }

            foreach ($deleted_rows as $k => $v) {
                $html .= '<tr>';
                $html .= '<td><b>' . $k . '</b></td>';
                $html .= '<td>' . Sanitize::sanitizeTagContent($this->renderDeltaValue($v)) . '</td>';
                $html .= '<td style="background-color: #eee;"></td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        foreach ($current_record_as_list as $k => $current_v) {
            if (!array_key_exists($k, $prev_record_as_list)) {
                continue;
            }

            $prev_v = $prev_record_as_list[$k];
            if ($current_v == $prev_v) {
                continue;
            }

            $html .= CompareHTML::drawCompare($prev_v, $current_v, $k);
        }

        return $html;
    }

    protected function renderDeltaValue(string $value): string
    {
        $limit = 300;

        if (strlen($value) < $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit) . '...';
    }

    /**
     * @param LoggerEntry $logger_entry
     * @return string
     */
    protected function getUserNameWithLinkForEntry(LoggerEntry $logger_entry): string
    {
        $user_full_id = $logger_entry->getUserFullId();
        $user_id = LoggerServiceProvider::getEntryService($this->container)->getUserIdForEntry($logger_entry);

        if (!$user_id) {
            return $user_full_id ?? '';
        }

        $user_service_container_name = LoggerConfig::getUserServiceContainerName();
        $user_profile_route_name = LoggerConfig::getUserProfileRouteName();
        if ($user_service_container_name === null || $user_service_container_name === '' || $user_profile_route_name === '') {
            return $user_full_id ?? '';
        }

        $user_service = $this->container->get($user_service_container_name);
        if (!$user_service instanceof InterfaceEntityService) {
            return $user_full_id ?? '';
        }

        $user_obj = $user_service->getById($user_id, false);
        if ($user_obj === null || !method_exists($user_obj, 'getName')) {
            return $user_full_id ?? '';
        }

        $user_name = $user_obj->getName();
        if (!is_string($user_name) && !$user_name instanceof \Stringable) {
            return $user_full_id ?? '';
        }

        $resolved_user_id = $user_obj->getId();
        if ($resolved_user_id === null) {
            return $user_full_id ?? '';
        }

        return '<a href="'
            . Sanitize::sanitizeTagContent(
                $this->urlFor($user_profile_route_name, ['user_id' => (string)$resolved_user_id])
            )
            . '">' . Sanitize::sanitizeTagContent((string)$user_name) . '</a>';
    }

    /**
     * @param int $record_id
     * @return string
     */
    protected function renderRecordHead(int $record_id): string
    {
        $entry_obj = $this->getRequiredEntry($record_id);

        $user_str = $this->getUserNameWithLinkForEntry($entry_obj);

        $html = '<dl class="dl-horizontal jumbotron" style="margin-top:20px;padding: 10px;">';
        $html .= '<dt style="padding: 5px 0;">Имя пользователя</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $user_str . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Время изменения</dt>';
        $html .= '<dd style="padding: 5px 0;">' . date('d.m H:i', $entry_obj->getCreatedAtTs()) . '</dd>';
        $html .= '<dt style="padding: 5px 0;">IP адрес</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getUserIp() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">URL</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getRequestUriWithServerName() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">User agent</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getHttpUserAgent() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Комментарий</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getComment() . '</dd>';
        $html .= '<dt style="padding: 5px 0;">Идентификатор</dt>';
        $html .= '<dd style="padding: 5px 0;">' . $entry_obj->getObjectFullid() . '</dd>';
        $html .= '</dl>';

        return $html;
    }

    /**
     * @param int $record_id
     * @return string
     * @throws \ReflectionException
     */
    protected function renderObjectFields(int $record_id): string
    {
        $html = '<h2>Все поля объекта</h2>';

        $record_obj = $this->getRequiredEntry($record_id);

        $record_objs = $this->unserializeLoggedValue($record_obj->getSerializedObject());

        $value_as_list = $this->convertValueToList($record_objs);
        ksort($value_as_list);

        $last_path = '';

        foreach ($value_as_list as $path => $value) {
            $path_to_display = Sanitize::sanitizeTagContent($path);

            if ($this->getPathWithoutLastElement($last_path) == $this->getPathWithoutLastElement($path)) {
                $elems = explode('.', $path);
                $last_elem = array_pop($elems);
                if (count($elems)) {
                    $path_to_display = '<span style="color: #999">'
                        . Sanitize::sanitizeTagContent(implode('.', $elems))
                        . '</span>.' . Sanitize::sanitizeTagContent((string)$last_elem);
                }
            }

            if (strlen($value) > 100) {
                $html .= '<div style="padding: 5px 0; border-bottom: 1px solid #ddd;">';

                $html .= '<div><b>' . $path_to_display . '</b></div>';
                $html .= '<div><pre style="white-space: pre-wrap;">' . Sanitize::sanitizeTagContent($value) . '</pre></div>';
                $html .= '</div>';
            } else {
                $html .= '<div style="padding: 5px 0; border-bottom: 1px solid #ddd;">';

                $html .= '<span style="padding-right: 50px;"><b>' . $path_to_display . '</b></span>';
                $html .= Sanitize::sanitizeTagContent($value);
                $html .= '</div>';
            }


            $last_path = $path;
        }

        return $html;
    }

    /**
     * @param \SplObjectStorage<object, mixed>|null $visited_objects
     * @return array<string, string>
     */
    protected function convertValueToList(
        mixed $value_value,
        string $value_path = '',
        ?\SplObjectStorage $visited_objects = null
    ): array
    {
        if ($value_value === null) {
            return [$value_path => '#NULL#'];
        }

        if (is_scalar($value_value)) {
            if (is_bool($value_value)) {
                return [$value_path => $value_value ? '#TRUE#' : '#FALSE#'];
            }

            return [$value_path => (string)$value_value];
        }

        $visited_objects ??= new \SplObjectStorage();
        /** @var array<array-key, mixed>|null $value_as_array */
        $value_as_array = null;
        $output_array = [];

        if (is_array($value_value)) {
            $value_as_array = $value_value;
        }

        if (is_object($value_value)) {
            if ($visited_objects->offsetExists($value_value)) {
                return [$value_path => '#RECURSION#'];
            }
            $visited_objects->offsetSet($value_value, null);
            $value_as_array = [];

            foreach (get_object_vars($value_value) as $property_name => $property_value) {
                $value_as_array[$property_name] = $property_value;
            }

            $reflect = new \ReflectionClass($value_value);
            $properties = $reflect->getProperties();

            foreach ($properties as $prop_obj) {
                // не показываем статические свойства класса - они не относятся к конкретному объекту
                // (например, это могут быть настройки CRUD для класса) и в журнале не нужны
                if ($prop_obj->isStatic()) {
                    continue;
                }
                if (!$prop_obj->isInitialized($value_value)) {
                    continue;
                }

                $name = $prop_obj->getName();
                $value = $prop_obj->getValue($value_value);
                $value_as_array[$name] = $value;
            }
        }

        if (!is_array($value_as_array)) {
            throw new \UnexpectedValueException('Не удалось привести значение к массиву');
        }

        foreach ($value_as_array as $key => $value) {
            $key_path = (string)$key;
            if ($value_path != '') {
                $key_path = $value_path . '.' . $key;
            }

            $value_output = $this->convertValueToList($value, $key_path, $visited_objects);
            $output_array = array_merge($output_array, $value_output);
        }

        return $output_array;
    }

    private function unserializeLoggedValue(string $serialized_value): mixed
    {
        // The logger stores application entities and therefore must restore their classes.
        // The logger database is consequently a trusted input boundary.
        $value = @unserialize($serialized_value, ['allowed_classes' => true]);
        if ($value === false && $serialized_value !== 'b:0;') {
            throw new \UnexpectedValueException('The logger entry contains invalid serialized data');
        }

        return $value;
    }

    private function getRequiredEntry(int $entry_id): LoggerEntry
    {
        $entry = LoggerServiceProvider::getEntryService($this->container)->getById($entry_id);
        if ($entry === null) {
            throw new \UnexpectedValueException('Logger entry ' . $entry_id . ' was not found');
        }

        return $entry;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getPathWithoutLastElement(string $path): string
    {
        $elems = explode('.', $path);
        array_pop($elems);

        return implode('.', $elems);
    }
}
