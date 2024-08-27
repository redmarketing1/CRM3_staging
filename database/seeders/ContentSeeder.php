<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContentTemplateLang;
use App\Models\Content;
use App\Models\User;

class ContentSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$notifications = [
			'new_holiday' => 'New Holiday',
			'new_meeting' => 'New Meeting',
			'new_event' => 'New Event',
			'new_lead' => 'New Lead',
			'lead_to_deal_conversion' => 'Lead to deal Conversation',
			'new_estimate' => 'New Estimate',
			'new_task_comment' => 'New Task Comment',
			'new_milestone' => 'New Milestone',
			'support_ticket' => 'Support Ticket',
			'new_company_policy' => 'Company Policy',
			'new_award' => 'New Award',
			'new_project' => 'New Project',
			'new_project_status' => 'New Project Status',
			'new_invoice' => 'New Invoice',
			'invoice_status' => 'Invoice Status',
			'new_deal' => 'New Deal',
			'new_task' => 'New Task',
			'task_moved' => 'Task Moved',
			'new_payment' => 'New Payment',
			'new_contract' => 'New Contract',
			'leave_status' => 'Leave Status',
			'new_trip' => 'New Trip',
			'estimation_email' => 'Estimation Email',
			'estimation_pdf_top' => 'Estimation PDF Top',
			'estimation_pdf_end' => 'Estimation PDF End',
			'material_cost' => 'Material Costs',
			'labor_cost' => 'Labor Costs',
			'disposal_costs' => 'Disposal Costs',
			'descriptions' => 'Descriptions',
			'total_costs' => 'Total Costs',
			'extract_number_prompt' => 'Extract Number Prompt',
			'progress_finalize' => 'Progress Finalize',
			'technical_description' => 'Technical Description',
			'generate_estimation' => 'Generate Estimation',
		];

		$defaultTemplate = [
			//New Holiday 
			'new_holiday' => [
				'variables' => '{
                        "Date": "date",
                        "Occasion": "occasion"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Date {date} Occasion {occasion}',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//New Meeting 
			'new_meeting' => [
				'variables' => '{
                        "Title": "title",
                        "Date": "date"
                        }',
				'lang' => [
					'ar' => 'اجتماع جديد {title} في {date}',
					'da' =>  'Nyt møde {title} den {date}',
					'de' => 'Neues Meeting {title} am {date}',
					'en' => 'New Meeting {title} on {date}',
					'es' => 'Nueva reunión {título} el {fecha}',
					'fr' => 'Nouvelle réunion {title} le {date}',
					'it' => 'Nuovo incontro {title} il giorno {date}',
					'ja' => '{date} の新しい会議 {title}',
					'nl' => 'Nieuwe vergadering {title} op {date}',
					'pl' => 'Nowe spotkanie {title} w dniu {date}',
					'ru' => 'Новая встреча {название} {дата}',
					'pt' => 'Nova reunião {title} em {date}',
				],
			],
			//New Event
			'new_event' => [
				'variables' => '{
                        "Event Title": "event_title",
                        "Department Name": "department_name",
                        "Start Date": "start_date",
                        "End Date": "end_date"
                        }',
				'lang' => [
					'ar' => "عنوان الحدث {event_title} Event Department {department_name} Start Date {start_date} End Date {end_date}",
					'da' => 'Begivenhedstitel {event_title} Begivenhedsafdeling {department_name} Startdato {start_date} Slutdato {end_date}',
					'de' => 'Titel der Veranstaltung {event_title} Abteilung der Veranstaltung {department_name} Startdatum {start_date} Enddatum {end_date}',
					'en' => 'Event Title {event_title} Event Department {department_name} Start Date {start_date} End Date {end_date}',
					'es' => 'Título del evento {event_title} Departamento del evento {department_name} Fecha de inicio {start_date} Fecha de finalización {end_date}',
					'fr' => 'Titre de lévénement {event_title} Service de lévénement {department_name} Date de début {start_date} Date de fin {end_date}',
					'it' => 'Titolo dellevento {event_title} Reparto evento {department_name} Data di inizio {start_date} Data di fine {end_date}',
					'ja' => '「イベント タイトル {event_title} イベント部門 {department_name} 開始日 {start_date} 終了日 {end_date}」',
					'nl' => 'Evenementtitel {event_title} Evenementafdeling {department_name} Startdatum {start_date} Einddatum {end_date}',
					'pl' => '„Tytuł wydarzenia {event_title} Dział wydarzenia {department_name} Data rozpoczęcia {start_date} Data zakończenia {end_date}',
					'ru' => 'Название мероприятия {event_title} Отдел мероприятия {department_name} Дата начала {start_date} Дата окончания {end_date}',
					'pt' => 'Título do evento {event_title} Departamento do evento {department_name} Data de início {start_date} Data de término {end_date}',
				],
			],
			//New Lead 
			'new_lead' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Lead Name": "lead_name",
                        "Lead Email": "lead_email"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء عميل محتمل جديد بواسطة {user_name}',
					'da' => 'Neuer Lead erstellt von {user_name}',
					'de' => 'Ny kundeemne oprettet af {user_name}',
					'en' => 'New Lead created by {user_name}',
					'es' => 'Nuevo cliente potencial creado por {user_name}',
					'fr' => 'Nouveau prospect créé par {user_name}',
					'it' => 'Nuovo lead creato da {user_name}',
					'ja' => '{user_name} によって作成された新しいリード',
					'nl' => 'Nieuwe lead gemaakt door {user_name}',
					'pl' => 'Nowy potencjalny klient utworzony przez użytkownika {user_name}',
					'ru' => 'Новый интерес создан пользователем {user_name}',
					'pt' => 'Novo lead criado por {user_name}',
				]
			],
			//lead_to_deal_conversion 
			'lead_to_deal_conversion' => [
				'variables' => '{
                        "Company Name": "user_name",
                         "Lead Name": "lead_name",
                        "Lead Email": "lead_email"
                        }',
				'lang' => [
					'ar' => 'تم تحويل الصفقة من خلال العميل المحتمل {lead_name}',
					'da' => 'Aftale konverteret via kundeemne {lead_name}',
					'de' => 'Geschäftsabschluss durch Lead {lead_name}',
					'en' => 'Deal converted through lead {lead_name}',
					'es' => 'Trato convertido a través del cliente potencial {lead_name}',
					'fr' => 'Offre convertie via le prospect {lead_name}',
					'it' => 'Offerta convertita tramite il lead {lead_name}',
					'ja' => 'リード {lead_name} を通じて商談が成立',
					'nl' => 'Deal geconverteerd via lead {lead_name}',
					'pl' => 'Umowa przekonwertowana przez lead {lead_name}',
					'ru' => 'Конвертация сделки через лид {lead_name}',
					'pt' => 'Negócio convertido por meio do lead {lead_name}',
				]
			],
			//New Estimate 
			'new_estimate' => [
				'variables' => '{
                        "Company Name": "user_name"
                        }',
				'lang' => [
					'ar' =>  'تم إنشاء التقدير الجديد بواسطة {user_name}',
					'da' => 'Nyt estimat oprettet af {user_name}',
					'de' => 'Neue Schätzung erstellt von {user_name}',
					'en' => 'New Estimation created by the {user_name}.',
					'es' => 'Nueva estimación creada por {user_name}',
					'fr' => 'Nouvelle estimation créée par {user_name}',
					'it' => 'Nuova stima creata da {user_name}',
					'ja' => '{user_name} によって作成された新しい見積もり',
					'nl' => 'Nieuwe schatting gemaakt door de {user_name}',
					'pl' => 'Nowa prognoza utworzona przez użytkownika {user_name}',
					'ru' => 'Новая оценка, созданная {user_name}',
					'pt' => 'Nova estimativa criada por {user_name}',
				]
			],
			//New Milestone 
			'new_milestone' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Title": "title",
                        "Cost": "cost",
                        "Start Date": "start_date",
                        "Due Date": "due_date"
                        }',
				'lang' => [
					'ar' => 'تمت إضافة مرحلة هامة جديدة {title} للتكلفة {cost} تاريخ البدء {start_date} وتاريخ الاستحقاق {due_date}',
					'da' => 'Ny milepæl tilføjet { title } af Cost { cost } Startdato { start_date } og Forfaldsdato { due_date }',
					'de' => 'Neu hinzugefügter Meilenstein { Titel } ​​der Kosten { Kosten } Startdatum { Startdatum } und Fälligkeitsdatum { Fälligkeitsdatum }',
					'en' => 'New Milestone added {title} of Cost {cost} Start Date {start_date} and Due Date {due_date}',
					'es' => 'Se agregó un nuevo hito {título} del costo {cost} fecha de inicio {start_date} y fecha de vencimiento {due_date}',
					'fr' => 'Nouveau jalon ajouté { title } de Coût { cost } Date de début { start_date } et Date déchéance { due_date }',
					'it' => 'Nuovo traguardo aggiunto { title } di Costo { cost } Data di inizio { start_date } e Data di scadenza { due_date }',
					'ja' => '新しいマイルストーンがコスト {cost} の {title} に追加されました 開始日 {start_date} と期日 {due_date}',
					'nl' => 'Nieuwe mijlpaal toegevoegd { titel } ​​van kosten { cost } Startdatum { start_date } en vervaldatum { due_date }',
					'pl' => 'Dodano nowy kamień milowy { tytuł } Koszt { koszt } Data rozpoczęcia { data_początkowa } i Termin { termin_data }',
					'ru' => 'Добавлен новый этап {название} стоимости {стоимость} Дата начала {start_date} и срок выполнения { due_date}',
					'pt' => 'Novo marco adicionado { title } de Custo { cost } Data de início { start_date } e Data de vencimento { due_date }',
				]
			],
			//New support_ticket 
			'support_ticket' => [
				'variables' => '{
                        "Support Priority": "support_priority",
                        "Support User Name": "support_user_name"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء بطاقة دعم جديدة ذات أولوية {support_priority} لـ {support_user_name}',
					'da' => 'Ny supportbillet oprettet med prioritet {support_priority} til {support_user_name}',
					'de' => 'Neues Support-Ticket mit Priorität {support_priority} für {support_user_name} erstellt',
					'en' => 'New Support ticket created of {support_priority} priority for {support_user_name}',
					'es' => 'Nuevo ticket de soporte creado con prioridad {support_priority} para {support_user_name}',
					'fr' => "Nouveau ticket d'assistance créé avec la priorité {support_priority} pour {support_user_name}",
					'it' => 'Nuovo ticket di assistenza creato con priorità {support_priority} per {support_user_name}',
					'ja' => '{support_user_name} の優先度 {support_priority} の新しいサポート チケットが作成されました',
					'nl' => 'Nieuw ondersteuningsticket gemaakt met prioriteit {support_priority} voor {support_user_name}',
					'pl' => 'Utworzono nowe zgłoszeninew_support_tickete do pomocy technicznej o priorytecie {support_priority} dla użytkownika {support_user_name}',
					'ru' => 'Создан новый запрос в службу поддержки с приоритетом {support_priority} для {support_user_name}',
					'pt' => 'Novo tíquete de suporte criado com prioridade {support_priority} para {support_user_name}',
				]
			],
			//New Task Comment 
			'new_task_comment' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Task Name": "task_name",
                        "Project Name": "project_name"
                        }',
				'lang' => [
					'ar' => 'تمت إضافة تعليق جديد في المهمة {task_name} للمشروع {project_name} بواسطة {user_name}',
					'da' => 'Ny kommentar tilføjet til opgave {task_name} i projektet {project_name} af {user_name}',
					'de' => 'Neuer Kommentar in Aufgabe {task_name} des Projekts {project_name} von {user_name} hinzugefügt',
					'en' => 'New Comment added in task {task_name} of project {project_name} by {user_name}',
					'es' => 'Nuevo comentario agregado en la tarea {task_name} del proyecto {project_name} por {user_name}',
					'fr' => 'Nouveau commentaire ajouté dans la tâche {task_name} du projet {project_name} par {user_name}',
					'it' => 'Nuovo commento aggiunto nell attività {task_name} del progetto {project_name} da {user_name}',
					'ja' => 'プロジェクト {project_name} のタスク {task_name} に {user_name} によって新しいコメントが追加されました',
					'nl' => 'Nieuwe opmerking toegevoegd in taak {task_name} van project {project_name} door {user_name}',
					'pl' => 'Nowy komentarz dodany w zadaniu {task_name} projektu {project_name} przez {user_name}',
					'ru' => 'Новый комментарий добавлен в задачу {task_name} проекта {project_name} пользователем {user_name}',
					'pt' => 'Novo comentário adicionado na tarefa {task_name} do projeto {project_name} por {user_name}',
				]
			],
			//New Company Policy
			'new_company_policy' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Company Policy Name": "company_policy_name"
                        }',
				'lang' => [
					'ar' => 'سياسة {company_policy_name} التي أنشأها {user_name}',
					'da' => '{company_policy_name}-politik oprettet af {user_name}',
					'de' => 'Richtlinie {company_policy_name} erstellt von {user_name}',
					'en' => '{company_policy_name} policy created by {user_name}',
					'es' => 'Política {company_policy_name} para la sucursal {user_name} creada',
					'fr' => 'Stratégie {company_policy_name} créée par {user_name}',
					'it' => 'norma di {company_policy_name} creata da {user_name}',
					'ja' => '{user_name} によって作成された {company_policy_name} ポリシー',
					'nl' => 'Beleid {company_policy_name} gemaakt door {user_name}',
					'pl' => 'Zasady firmy {company_policy_name} utworzone przez użytkownika {user_name}',
					'ru' => 'Создана политика {company_policy_name} для филиала {user_name}',
					'pt' => 'Política {company_policy_name} criada por {user_name}',
				]
			],
			//New Award
			'new_award' => [
				'variables' => '{
                        "Award Name": "award_name",
                        "Employee Name": "employee_name",
                        "Award Date": "award_date"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء {award_name} لـ {employee_name} من {Award_date}',
					'da' => '{award_name} oprettet til {employee_name} fra {award_date}',
					'de' => '{award_name} erstellt für {employee_name} vom {award_date}',
					'en' => '{award_name} created for {employee_name} from {award_date}',
					'es' => '{award_name} creado para {employee_name} de {award_date}',
					'fr' => '{award_name} créé pour {employee_name} à partir du {award_date}',
					'it' => '{award_name} creato per {employee_name} da {award_date}',
					'ja' => '{employee_name} のために {award_name} が {award_date} から作成されました',
					'nl' => '{award_name} gemaakt voor {employee_name} vanaf {award_date}',
					'pl' => '{award_name} utworzone dla {employee_name} od {award_date}',
					'ru' => '{award_name} создано для {employee_name} с {award_date}',
					'pt' => '{award_name} criado para {employee_name} de {award_date}',
				]
			],
			//New Project
			'new_project' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Project Name": "project_name"
                        }',
				'lang' => [
					'ar' => 'تم تكوين مشروع جديد { project_name } بواسطة { user_name }',
					'da' => 'Nyt { project_name } projekt oprettet af { user_name }',
					'de' => 'Neues Projekt {project_name} erstellt von {user_name}',
					'en' => 'New {project_name} project created by {user_name}.',
					'es' => 'Nuevo proyecto {project_name} creado por {user_name}',
					'fr' => 'Nouveau projet { project_name } créé par { nom_utilisateur }',
					'it' => 'Nuovo progetto {project_name} creato da {user_name}',
					'ja' => '{user_name} によって作成された新規 {project_name} プロジェクト',
					'nl' => 'Nieuw project { project_name } gemaakt door { user_name }',
					'pl' => 'Nowy projekt {project_name } utworzony przez użytkownika {user_name }',
					'ru' => 'Новый проект { project_name }, созданный пользователем { user_name }',
					'pt' => 'Novo projeto {project_name} criado por {user_name}',
				]
			],
			//New Project status
			'new_project_status' => [
				'variables' => '{
                         "Project Name": "project_name",
                         "Status": "status"
 
                        }',
				'lang' => [
					'ar' =>  'تم تحديث حالة {project_name} الجديدة {status} بنجاح',
					'da' => 'Ny {project_name}-status blev opdateret {status}',
					'de' => 'Neuer Status {project_name} {Status} erfolgreich aktualisiert',
					'en' => 'New {project_name} Status Updadated {status} successfully.',
					'es' => 'Nuevo estado de {project_name} actualizado {status} con éxito',
					'fr' => 'Nouveau statut de {project_name} {status} mis à jour avec succès',
					'it' => 'Nuovo stato {project_name} Aggiornato {status} con successo',
					'ja' => '新しい {project_name} ステータス {status} が正常に更新されました',
					'nl' => 'Nieuwe {project_name}-status {status} succesvol bijgewerkt',
					'pl' => 'Nowy status {project_name} Zaktualizowano {status} pomyślnie',
					'ru' => 'Новый статус {project_name} успешно обновлен {статус}',
					'pt' => 'Novo status {project_name} atualizado {status} com sucesso',
				]
			],
			//New Invoice
			'new_invoice' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Invoice Number": "invoice_number"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء الفاتورة الجديدة {invoice_number} بواسطة {user_name}',
					'da' => 'Ny faktura {invoice_number} oprettet af {user_name}',
					'de' => 'Neue Rechnung {invoice_number} erstellt von {user_name}',
					'en' => 'New Invoice { invoice_number } created by {user_name}',
					'es' => 'Nueva factura {invoice_number} creada por {user_name}',
					'fr' => 'Nouvelle facture {invoice_number} créée par {user_name}',
					'it' => 'Nuova fattura {invoice_number} creata da {user_name}',
					'ja' => '{user_name} によって作成された新しい請求書 {invoice_number}',
					'nl' => 'Nieuwe factuur {invoice_number} gemaakt door {user_name}',
					'pl' => 'Nowa faktura {invoice_number} utworzona przez użytkownika {user_name}',
					'ru' => 'Новый счет {invoice_number}, созданный {user_name}',
					'pt' => 'Nova fatura {invoice_number} criada por {user_name}',
				]
			],
			'invoice_status' => [
				'variables' => '{
                        "Invoice": "invoice",
                        "Old status": "old_status",
                        "New Status": "status"
                         }',
				'lang' => [
					'ar' => 'تم تغيير حالة الفاتورة {الفاتورة} من {old_status} إلى {status}',
					'da' => 'Faktura {invoice}-status ændret fra {old_status} til {status}',
					'de' => 'Status der Rechnung {invoice} von {old_status} in {status} geändert',
					'en' => 'Invoice {invoice} status changed from {old_status} to {status}',
					'es' => 'El estado de la factura {factura} cambió de {old_status} a {status}',
					'fr' => 'Le statut de la facture {invoice} est passé de {old_status} à {status}',
					'it' => 'Lo stato della fattura {invoice} è cambiato da {old_status} a {status}',
					'ja' => '請求書 {invoice} のステータスが {old_status} から {status} に変更されました',
					'nl' => 'Factuur {factuur} status gewijzigd van {old_status} in {status}',
					'pl' => 'Faktura {invoice} zmieniła stan z {old_status} na {status}',
					'ru' => 'Статус счета-фактуры {invoice} изменен с {old_status} на {status}',
					'pt' => 'Status da fatura {invoice} alterado de {old_status} para {status}',
				]
			],
			'new_deal' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Deal Name": "deal_name"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء الصفقة الجديدة بواسطة {user_name}',
					'da' => 'Ny aftale oprettet af {user_name}',
					'de' => 'Neuer Deal erstellt von {user_name}',
					'en' => 'New Deal created by {user_name}',
					'es' => 'Nueva oferta creada por {user_name}',
					'fr' => 'Nouvelle offre créée par {user_name}',
					'it' => 'New Deal creato da {user_name}',
					'ja' => '{user_name} によって作成された新しいディール',
					'nl' => 'Nieuwe deal gemaakt door {user_name}',
					'pl' => 'Nowa oferta utworzona przez użytkownika {user_name}',
					'ru' => 'Новая сделка создана пользователем {user_name}',
					'pt' => 'Novo negócio criado por {user_name}',
				]
			],
			//New Task    
			'new_task' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Task Name": "task_name",
                        "Project Name": "project_name"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء مهمة {task_name} لمشروع {project_name} بواسطة {user_name}',
					'da' => '{task_name} opgave oprettet for {project_name}-projekt af {user_name}',
					'de' => 'Aufgabe {task_name} erstellt für Projekt {project_name} von {user_name}',
					'en' => '{task_name} task create for {project_name} project by {user_name}.',
					'es' => '{task_name} tarea creada para {project_name} proyecto por {user_name}',
					'fr' => 'Tâche {task_name} créée pour le projet {project_name} par {user_name}',
					'it' => 'Attività {task_name} creata per il progetto {project_name} da {user_name}',
					'ja' => '{user_name} による {project_name} プロジェクトの {task_name} タスク作成',
					'nl' => '{task_name} taak gemaakt voor {project_name} project door {user_name}',
					'pl' => 'Zadanie {task_name} utworzono dla projektu {project_name} przez użytkownika {user_name}',
					'ru' => 'Задача {task_name} создана для проекта {project_name} пользователем {user_name}',
					'pt' => 'Tarefa {task_name} criada para o projeto {project_name} por {user_name}',
				]
			],
			//Task Moved   
			'task_moved' => [
				'variables' => '{
                        "Task Title": "task_title",
                        "Old Task Stages": "task_stage",
                        "New Task Stages": "new_task_stage"
                        }',
				'lang' => [
					'ar' => 'المهمة {task_title} تغيير المرحلة من {task_stage} إلى {new_task_stage}',
					'da' => 'Opgave {task_title} Faseændring fra {task_stage} til {new_task_stage}',
					'de' => 'Aufgabe {task_title} Phasenwechsel von {task_stage} zu {new_task_stage}',
					'en' => 'Task {task_title} Stage change from {task_stage} to {new_task_stage}',
					'es' => 'Tarea {task_title} Cambio de etapa de {task_stage} a {new_task_stage}',
					'fr' => 'Tâche {task_title} Changement détape de {task_stage} à {new_task_stage}',
					'it' => 'Attività {task_title} Cambio fase da {task_stage} a {new_task_stage}',
					'ja' => 'タスク {task_title} ステージが {task_stage} から {new_task_stage} に変更されました',
					'nl' => 'Taak {task_title} Stage verandering van {task_stage} naar {new_task_stage}',
					'pl' => 'Zmiana etapu zadania {task_title} z {task_stage} na {new_task_stage}',
					'ru' => 'Стадия задачи {task_title} изменена с {task_stage} на {new_task_stage}',
					'pt' => 'Mudança de estágio da tarefa {task_title} de {task_stage} para {new_task_stage}',
				]
			],
			//Task Moved   
			'new_payment' => [
				'variables' => '{
                        "User Name": "user_name",
                        "Amount": "amount",
                        "Created By": "created_by"
                         }',
				'lang' => [
					'ar' => 'تم إنشاء دفعة جديدة بمبلغ {amount} من أجل {user_name} بواسطة {created_by}',
					'da' => 'Ny betaling på {amount} oprettet for {user_name} oprettet af {created_by}',
					'de' => 'Neue Zahlung in Höhe von {amount} erstellt für {user_name} Erstellt von {created_by}',
					'en' => 'New payment of {amount} created for {user_name} Created By {created_by}',
					'es' => 'Nuevo pago de {amount} creado para {user_name} Creado por {created_by}',
					'fr' => 'Nouveau paiement de {amount} créé pour {user_name} Créé par {created_by}',
					'it' => 'Nuovo pagamento di {amount} creato per {user_name} creato da {created_by}',
					'ja' => '{user_name} のために作成された {amount} の新しい支払い {created_by} によって作成されました',
					'nl' => 'Nieuwe betaling van {amount} gemaakt voor {user_name} Gemaakt door {created_by}',
					'pl' => 'Nowa płatność w wysokości {amount} została utworzona dla użytkownika {user_name} Utworzona przez {created_by}',
					'ru' => 'Создан новый платеж на {сумму} для {user_name} Создано {created_by}',
					'pt' => 'Novo pagamento de {amount} criado para {user_name} Criado por {created_by}',
				]
			],
			//New Contract
			'new_contract' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "Contract Name": "contract_subject",
                        "Client Name": "contract_client",
                        "Contract Price": "contract_value",
                        "Contract Start Date": "contract_start_date",
                        "Contract End Date": "contract_end_date"
                        }',
				'lang' => [
					'ar' => 'تم إنشاء عقد {Contract_subject} لـ {contract_client} بواسطة {user_name}',
					'da' => '{contract_subject} kontrakt oprettet for {contract_client} af {user_name}',
					'de' => '{contract_subject} Vertrag erstellt für {contract_client} von {user_name}',
					'en' => '{contract_subject} contract created for {contract_client} by {user_name}',
					'es' => '{contract_subject} contrato creado para {contract_client} por {user_name}',
					'fr' => 'Contrat {contract_subject} créé pour {contract_client} par {user_name}',
					'it' => 'Contratto {contract_subject} creato per {contract_client} da {user_name}',
					'ja' => '{user_name} によって {contract_client} のために作成された {contract_subject} 契約',
					'nl' => '{contract_subject} contract gemaakt voor {contract_client} door {user_name}',
					'pl' => 'Umowa {contract_subject} utworzona dla {contract_client} przez {user_name}',
					'ru' => 'Контракт {contract_subject} создан для {contract_client} пользователем {user_name}',
					'pt' => 'Contrato {contract_subject} criado para {contract_client} por {user_name}',
				]
			],
			// /leave_status
			'leave_status' => [
				'variables' => '{
                        "Company Name": "user_name",
                        "status": "status"
                        }',
				'lang' => [
					'ar' => 'كانت المغادرة {status} بواسطة {user_name}',
					'da' => 'Orlov har været {status} af {user_name}',
					'de' => 'Verlassen wurde {status} von {user_name}',
					'en' => 'Leave has been {status} by {user_name}',
					'es' => 'La licencia ha sido {status} por {user_name}',
					'fr' => 'Le congé a été {status} par {user_name}',
					'it' => 'Il congedo è stato {status} di {user_name}',
					'ja' => '{user_name} さんによる {status} の休暇',
					'nl' => 'Verlof is {status} door {user_name}',
					'pl' => 'Urlop został {status} przez {user_name}',
					'ru' => 'Выход был {status} от {user_name}',
					'pt' => 'A saída foi {status} de {user_name}',
				]
			],
			//new_trip 
			'new_trip' => [
				'variables' => '{
                        "Purpose Of Visit": "purpose_of_visit",
                        "Place Of Visit": "place_of_visit",
                        "Start Date": "start_date",
                        "End Date": "end_date"
                        }',
				'lang' => [
					'ar' => 'يبدأ مكان الزيارة الجديد في {place_of_visit} لغرض {الغرض_من_فيزيت} من {start_date} إلى {end_date}',
					'da' => 'Nyt besøgssted på {place_of_visit} til formålet {purpose_of_visit} start fra {start_date} til {end_date}',
					'de' => 'Neuer Besuchsort in {place_of_visit} für den Zweck {purpose_of_visit} von {start_date} bis {end_date}',
					'en' => 'New Place of visit at {place_of_visit} for purpose {purpose_of_visit} start from {start_date} to {end_date}',
					'es' => 'Nuevo lugar de visita en {place_of_visit} para el propósito {purpose_of_visit} desde {start_date} hasta {end_date}',
					'fr' => 'Nouveau lieu de visite à {place_of_visit} dans le but {purpose_of_visit} à partir du {start_date} jusqu au {end_date}',
					'it' => 'Nuovo luogo di visita a {place_of_visit} per lo scopo {purpose_of_visit} a partire dal {start_date} al {end_date}',
					'ja' => '{place_of_visit} での目的 {purpose_of_visit} の新しい訪問場所は {start_date} から {end_date} までです',
					'nl' => 'Nieuwe plaats van bezoek op {place_of_visit} voor doel {purpose_of_visit} start van {start_date} tot {end_date}',
					'pl' => 'Nowe miejsce wizyty w {place_of_visit} w celu {purpose_of_visit} rozpoczyna się od {start_date} do {end_date}',
					'ru' => 'Новое место посещения в {place_of_visit} с целью {цель_посещения}, начало с {start_date} по {end_date}',
					'pt' => 'Novo local de visita em {place_of_visit} para o propósito {purpose_of_visit} começa de {start_date} a {end_date}',
				]
			],
			//estimation_email 
			'estimation_email' => [
				'variables' => '{
                        "Estimation Title": "estimation.title",
                        "Client Name": "client_name",
                        "Company Name": "client.company_name",
                        "Salutation": "client.salutation",
                        "Title": "client.title",
                        "First Name": "client.first_name",
                        "Last Name": "client.last_name",
                        "Email": "client.email",
                        "Phone": "client.phone",
                        "Mobile": "client.mobile",
                        "Website": "client.website",
                        "Stree + N": "construction.street",
                        "Additional Address": "construction.additional_address",
                        "Zipcode": "construction.zipcode",
                        "City": "construction.city",
                        "State": "construction.state",
                        "Country": "construction.country",
                        "Tax Number": "construction.tax_number",
                        "Notes": "construction.notes",
                        "Current Date": "current.date+21days"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Hallo {client.salutation} {client_name},

                        vielen Dank für Ihre Anfrage und den netten Besichtigungstermin vor Ort.
                        
                        Wie besprochen haben unsere Bauleiter nun Ihre Anfrage im Detail kalkuliert. Im Anhang erhalten Sie unser Angebot zu Ihrem Bauvorhaben in {construction.street} {construction.city}.
                        
                        Wir haben uns Zeit für die Planung genommen und alle besprochenen Details so weit wie möglich in unserem Angebot berücksichtigt. Unser Team hat schon viele Sanierungen durchgeführt und aufgrund unserer Erfahrung können wir Ihnen eine sehr saubere Umsetzung zusichern.
                        
                        Zeitlicher Plan: ca. 2-3 Monate, beginnend ab {current.date+21days}
                        Zahlungsplan: stufenweise nach Abnahmeprotokoll
                        Im Anhang finden Sie unseren Standard-Bauvertrag nach VOB, in dem alle wichtigen Punkte zu Ihrem Bauvorhaben geregelt sind.
                        
                        Wenn Sie unser Team mit der Umsetzung beauftragen möchten, bitten wir Sie uns gerne kurzfristig Bescheid zu geben, damit wir das von den Kapazitäten her einplanen können. Im Anschluss folgt dann eine finale Baubesprechung vor Ort.
                        
                        Für den Fall, dass Sie ein alternatives Angebot vorliegen haben, möchten wir Sie bitten, uns die Möglichkeit zu geben, dieses zu prüfen. Wir können dann gerne gemeinsam mit Ihnen und unseren Bauleitern besprechen, was wir an unserem Angebot ggf. an Leistungen oder Positionen anpassen können.
                        
                        Bei Fragen können wir natürlich gerne telefonieren oder uns nochmal vor Ort treffen.
                        Wir würden uns über eine Zusammenarbeit freuen. Danke im Voraus für Ihre zeitnahe Rückmeldung.',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//estimation_pdf_top 
			'estimation_pdf_top' => [
				'variables' => '{
                        "Estimation Title": "estimation.title",
                        "Client Name": "client_name",
                        "Company Name": "client.company_name",
                        "Salutation": "client.salutation",
                        "Title": "client.title",
                        "First Name": "client.first_name",
                        "Last Name": "client.last_name",
                        "Email": "client.email",
                        "Phone": "client.phone",
                        "Mobile": "client.mobile",
                        "Website": "client.website",
                        "Stree + N": "construction.street",
                        "Additional Address": "construction.additional_address",
                        "Zipcode": "construction.zipcode",
                        "City": "construction.city",
                        "State": "construction.state",
                        "Country": "construction.country",
                        "Tax Number": "construction.tax_number",
                        "Notes": "construction.notes",
                        "Current Date": "current.date+21days"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//estimation_pdf_end 
			'estimation_pdf_end' => [
				'variables' => '{
                        "Estimation Title": "estimation.title",
                        "Client Name": "client_name",
                        "Company Name": "client.company_name",
                        "Salutation": "client.salutation",
                        "Title": "client.title",
                        "First Name": "client.first_name",
                        "Last Name": "client.last_name",
                        "Email": "client.email",
                        "Phone": "client.phone",
                        "Mobile": "client.mobile",
                        "Website": "client.website",
                        "Stree + N": "construction.street",
                        "Additional Address": "construction.additional_address",
                        "Zipcode": "construction.zipcode",
                        "City": "construction.city",
                        "State": "construction.state",
                        "Country": "construction.country",
                        "Tax Number": "construction.tax_number",
                        "Notes": "construction.notes",
                        "Current Date": "current.date+21days"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//material_cost
			'material_cost' => [
				'variables' => '{
                        "Field One": "field1",
                        "Position Nr": "Position-Nr",
                        "Name": "Name",
                        "Description": "Description",
                        "Quantity": "Quantity",
                        "Unit": "Unit"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//labor_cost
			'labor_cost' => [
				'variables' => '{
                        "Field One": "field1",
                        "Position Nr": "Position-Nr",
                        "Name": "Name",
                        "Description": "Description",
                        "Quantity": "Quantity",
                        "Unit": "Unit"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//disposal_costs
			'disposal_costs' => [
				'variables' => '{
                        "Field One": "field1",
                        "Position Nr": "Position-Nr",
                        "Name": "Name",
                        "Description": "Description",
                        "Quantity": "Quantity",
                        "Unit": "Unit"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//descriptions
			'descriptions' => [
				'variables' => '{
                        "Field One": "field1",
                        "Position Nr": "Position-Nr",
                        "Name": "Name",
                        "Description": "Description",
                        "Quantity": "Quantity",
                        "Unit": "Unit"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//total_costs
			'total_costs' => [
				'variables' => '{
                        "Field One": "field1",
                        "Position Nr": "Position-Nr",
                        "Name": "Name",
                        "Description": "Description",
                        "Quantity": "Quantity",
                        "Unit": "Unit"
                        }',
				'lang' => [
					'ar' => "التاريخ {date} المناسبة {المناسبة}",
					'da' =>  'Dato {date} Anledning {occasion}',
					'de' => 'Datum {Datum} Anlass {Gelegenheit}',
					'en' => 'Test',
					'es' => 'Fecha {fecha} Ocasión {ocasión}',
					'fr' => 'Date {date} Occasion {occasion}',
					'it' => 'Data {data} Occasione {occasione}',
					'ja' => '「日付 {date} 行事 {occasion}」',
					'nl' => 'Datum {datum} Gelegenheid {gelegenheid}',
					'pl' => '„Data {data} Okazja {okazja}”',
					'ru' => 'Дата {дата} событие {случай}',
					'pt' => 'Data {data} Ocasião {ocasião}',
				],
			],
			//extract_number_prompt
			'extract_number_prompt' => [
				'variables' => '{
						"Extracted Value": "extracted_value"
						}',
				'lang' => [
					'de' => 'Test',
					'en' => 'Test',
				],
			],
			//progress_finalize
			'progress_finalize' => [
				'variables' => '{
							"Estimation Title": "estimation.title",
							"Client Name": "client_name",
							"Company Name": "client.company_name",
							"Salutation": "client.salutation",
							"Title": "client.title",
							"First Name": "client.first_name",
							"Last Name": "client.last_name",
							"Email": "client.email",
							"Phone": "client.phone",
							"Mobile": "client.mobile",
							"Website": "client.website",
							"Stree + N": "construction.street",
							"Additional Address": "construction.additional_address",
							"Zipcode": "construction.zipcode",
							"City": "construction.city",
							"State": "construction.state",
							"Country": "construction.country",
							"Tax Number": "construction.tax_number",
							"Notes": "construction.notes",
							"Current Date": "current.date+21days"
						}',
				'lang' => [
					'de' => 'Test',
					'en' => 'Test',
				],
			],
			//technical_description
			'technical_description' => [
				'variables' => '',
				'lang' => [
					'de' => 'Test',
					'en' => 'Test',
				],
			],
			//generate_estimation
			'generate_estimation' => [
				'variables' => '',
				'lang' => [
					'de' => 'Test',
					'en' => 'Test',
				],
			],
		];

		$user = User::where('type', 'super admin')->first();
		$ai_templates = array(
			'material_cost',
			'labor_cost',
			'disposal_costs',
			'descriptions',
			'total_costs',
			'extract_number_prompt',
			'technical_description',
			'generate_estimation',
		);
		foreach ($notifications as $k => $n) {
			$ntfy = Content::where('slug', $k)->count();
			if ($ntfy == 0) {

				$new = new Content();
				$new->name = $n;
				$new->slug = $k;
				if (in_array($k, $ai_templates)) {
					$new->is_ai = 1;
				}
				$new->save();

				foreach ($defaultTemplate[$k]['lang'] as $lang => $content) {
					ContentTemplateLang::create(
						[
							'parent_id' => $new->id,
							'lang' => $lang,
							'variables' => $defaultTemplate[$k]['variables'],
							'content' => $content,
							'created_by' => !empty($user) ? $user->id : 1,
							'workspace' => 0,
						]
					);
				}
			}
		}
	}
}
