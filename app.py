# app.py
from flask import Flask, request, jsonify
from openai import OpenAI, APIConnectionError, APIStatusError
import os
from dotenv import load_dotenv
from flask_cors import CORS
import json
from flask_sqlalchemy import SQLAlchemy
from datetime import datetime

# Charger les variables d'environnement depuis le fichier .env
load_dotenv()

app = Flask(__name__)
# Permettre les requêtes CORS depuis le frontend PHP
CORS(app) 

# --- Configuration de la base de données SQLite ---
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///nexusai.db' # Nom du fichier de la base de données
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False # Désactive le suivi des modifications pour économiser des ressources

db = SQLAlchemy(app) # Initialisation de l'objet de base de données

# --- Définition des modèles de base de données ---
class Sale(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    quarter = db.Column(db.String(10), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    product = db.Column(db.String(100), nullable=False)
    date_recorded = db.Column(db.DateTime, default=datetime.utcnow)

    def to_dict(self):
        return {
            'id': self.id,
            'quarter': self.quarter,
            'amount': self.amount,
            'product': self.product,
            'date_recorded': self.date_recorded.isoformat()
        }

class Meeting(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    subject = db.Column(db.String(200), nullable=False)
    date = db.Column(db.String(10), nullable=False) # Format AAAA-MM-JJ
    time = db.Column(db.String(5), nullable=False)  # Format HH:MM
    participants = db.Column(db.String(500)) # Stocké comme une chaîne JSON ou séparée par des virgules
    status = db.Column(db.String(50), default='Scheduled')
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def to_dict(self):
        return {
            'id': self.id,
            'subject': self.subject,
            'date': self.date,
            'time': self.time,
            'participants': json.loads(self.participants) if self.participants else [],
            'status': self.status,
            'created_at': self.created_at.isoformat()
        }

class Client(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False, unique=True)
    email = db.Column(db.String(100), nullable=True)
    company = db.Column(db.String(100), nullable=True)
    status = db.Column(db.String(50), nullable=True) # e.g., Active, Lead, Inactive

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'email': self.email,
            'company': self.company,
            'status': self.status
        }

class Ticket(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    ticket_id_str = db.Column(db.String(50), nullable=False, unique=True) # Ex: TICKET-001
    subject = db.Column(db.String(200), nullable=False)
    status = db.Column(db.String(50), nullable=False) # e.g., Ouvert, Fermé, En cours
    assigned_to = db.Column(db.String(100), nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def to_dict(self):
        return {
            'id': self.id,
            'ticket_id_str': self.ticket_id_str,
            'subject': self.subject,
            'status': self.status,
            'assigned_to': self.assigned_to,
            'created_at': self.created_at.isoformat()
        }

class SystemStatus(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    system_name = db.Column(db.String(100), unique=True, nullable=False)
    status = db.Column(db.String(50), nullable=False)
    last_checked = db.Column(db.DateTime, nullable=False)
    def to_dict(self):
        return {
            "id": self.id,
            "system_name": self.system_name,
            "status": self.status,
            "last_checked": self.last_checked.isoformat()
        }

class Email(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    recipient = db.Column(db.String(200), nullable=False)
    subject = db.Column(db.String(200), nullable=False)
    body = db.Column(db.Text, nullable=True)
    sent_at = db.Column(db.DateTime, default=datetime.utcnow)
    status = db.Column(db.String(50), default='Sent') # Ex: Sent, Failed, Draft

    def to_dict(self):
        return {
            'id': self.id,
            'recipient': self.recipient,
            'subject': self.subject,
            'body': self.body,
            'sent_at': self.sent_at.isoformat(),
            'status': self.status
        }

class ArchivedDocument(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    document_id_str = db.Column(db.String(100), nullable=False, unique=True)
    document_type = db.Column(db.String(100), nullable=False)
    tags = db.Column(db.String(500), nullable=True) # Stocké comme une chaîne JSON
    archived_at = db.Column(db.DateTime, default=datetime.utcnow)
    location = db.Column(db.String(200), nullable=True) # Ex: 'Cloud Storage', 'Local Server'

    def to_dict(self):
        return {
            'id': self.id,
            'document_id_str': self.document_id_str,
            'document_type': self.document_type,
            'tags': json.loads(self.tags) if self.tags else [],
            'archived_at': self.archived_at.isoformat(),
            'location': self.location
        }

class Employee(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(100), nullable=False, unique=True)
    position = db.Column(db.String(100), nullable=True)
    department = db.Column(db.String(100), nullable=True)
    email = db.Column(db.String(100), nullable=True)
    phone = db.Column(db.String(50), nullable=True)
    hire_date = db.Column(db.String(10), nullable=True) # Format AAAA-MM-JJ

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'position': self.position,
            'department': self.department,
            'email': self.email,
            'phone': self.phone,
            'hire_date': self.hire_date
        }

class ProjectTask(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    project_name = db.Column(db.String(100), nullable=False)
    task_description = db.Column(db.String(500), nullable=False)
    due_date = db.Column(db.String(10), nullable=True) # Format AAAA-MM-JJ
    assigned_to = db.Column(db.String(100), nullable=True)
    status = db.Column(db.String(50), default='Open') # Ex: Open, In Progress, Completed, Blocked
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

    def to_dict(self):
        return {
            'id': self.id,
            'project_name': self.project_name,
            'task_description': self.task_description,
            'due_date': self.due_date,
            'assigned_to': self.assigned_to,
            'status': self.status,
            'created_at': self.created_at.isoformat()
        }


# --- Initialisation de la base de données et chargement des données initiales ---
with app.app_context():
    db.create_all() # Crée les tables si elles n'existent pas

    # Charger les données initiales si la base est vide
    if Sale.query.count() == 0:
        print("Chargement des données de vente initiales...")
        sales = [
            Sale(quarter='T1', amount=1250000, product='Nexus Alpha'),
            Sale(quarter='T2', amount=1780000, product='Nexus X200'),
            Sale(quarter='T3', amount=1500000, product='Nexus Beta'),
            Sale(quarter='T4', amount=2100000, product='Nexus Prime')
        ]
        db.session.add_all(sales)
        db.session.commit()
        print("Données de vente chargées.")

    if Client.query.count() == 0:
        print("Chargement des données client initiales...")
        clients = [
            Client(name="Alice Dupont", email="alice.dupont@example.com", company="Tech Solutions Inc.", status="Active"),
            Client(name="Bob Martin", email="bob.martin@example.com", company="Global Innovations", status="Lead")
        ]
        db.session.add_all(clients)
        db.session.commit()
        print("Données client chargées.")

    if Ticket.query.count() == 0:
        print("Chargement des données de ticket initiales...")
        tickets = [
            Ticket(ticket_id_str="TICKET-001", subject="Problème de connexion", status="Ouvert", assigned_to="Support Team"),
            Ticket(ticket_id_str="TICKET-002", subject="Demande de fonctionnalité", status="Fermé", assigned_to="Product Team")
        ]
        db.session.add_all(tickets)
        db.session.commit()
        print("Données de ticket chargées.")

    if SystemStatus.query.count() == 0:
        print("Chargement des statuts des systèmes initiaux...")
        statuses = [
            SystemStatus(system_name="CRM System", status="online"),
            SystemStatus(system_name="ERP System", status="online"),
            SystemStatus(system_name="HR Platform", status="offline")
        ]
        db.session.add_all(statuses)
        db.session.commit()
        print("Statuts des systèmes chargés.")
    
    if Employee.query.count() == 0:
        print("Chargement des données d'employés initiales...")
        employees = [
            Employee(name="Charles Dubois", position="Développeur Senior", department="R&D", email="charles.dubois@example.com", phone="555-1234", hire_date="2020-01-15"),
            Employee(name="Émilie Bernard", position="Chef de Projet", department="Opérations", email="emilie.bernard@example.com", phone="555-5678", hire_date="2018-06-01")
        ]
        db.session.add_all(employees)
        db.session.commit()
        print("Données d'employés chargées.")

    if ProjectTask.query.count() == 0:
        print("Chargement des données de tâches de projet initiales...")
        tasks = [
            ProjectTask(project_name="Projet Alpha", task_description="Développement de la fonctionnalité X", due_date="2025-08-01", assigned_to="Charles Dubois", status="In Progress"),
            ProjectTask(project_name="Projet Beta", task_description="Rédaction du rapport financier", due_date="2025-07-20", assigned_to="Émilie Bernard", status="Open")
        ]
        db.session.add_all(tasks)
        db.session.commit()
        print("Données de tâches de projet chargées.")


# Récupérer la clé API OpenAI depuis les variables d'environnement
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")

# Vérifier si la clé API est disponible
if not OPENAI_API_KEY:
    print("Erreur: La clé API OpenAI n'est pas configurée dans le fichier .env.")

# Initialiser le client OpenAI globalement pour éviter de le recréer à chaque requête
client = OpenAI(api_key=OPENAI_API_KEY)


# --- Fonctions qui interagissent avec la base de données ---

def get_sales_data(quarter: str = None) -> dict:
    """
    Récupère les chiffres de vente pour un trimestre spécifique ou toutes les données de vente depuis la DB.
    """
    if quarter:
        normalized_quarter = quarter.upper().replace('Q', 'T')
        sale = Sale.query.filter_by(quarter=normalized_quarter).first()
        if sale:
            return {normalized_quarter: sale.to_dict()}
        else:
            return {"error": f"Données de vente non trouvées pour le trimestre {quarter}."}
    
    all_sales = Sale.query.all()
    return {s.quarter: s.to_dict() for s in all_sales}

def schedule_meeting(subject: str, date: str, time: str, participants: list = None) -> dict:
    """
    Planifie une nouvelle réunion et l'enregistre dans la base de données.
    """
    try:
        new_meeting = Meeting(
            subject=subject,
            date=date,
            time=time,
            participants=json.dumps(participants) if participants else "[]"
        )
        db.session.add(new_meeting)
        db.session.commit()
        return {
            "status": "success",
            "message": f"Réunion '{subject}' planifiée pour le {date} à {time} avec {', '.join(participants) if participants else 'personne spécifique'} et enregistrée.",
            "details": new_meeting.to_dict()
        }
    except Exception as e:
        db.session.rollback()
        return {"status": "error", "message": f"Erreur lors de la planification de la réunion: {e}"}

def generate_report(report_type: str, period: str = None) -> dict:
    """
    Génère un rapport spécifique. Les données proviennent de la DB si pertinent.
    """
    if report_type.lower() == 'ventes':
        sales_data = get_sales_data(period) # Réutilise la fonction de vente
        return {
            "status": "success",
            "message": f"Rapport de ventes pour la période '{period if period else 'toutes les périodes'}' généré.",
            "report_details": {
                "type": "ventes",
                "period": period,
                "data": sales_data,
                "url": f"https://example.com/reports/sales_{period if period else 'all'}.pdf"
            }
        }
    elif report_type.lower() == 'financier':
        # Simuler des données financières de la DB
        return {
            "status": "success",
            "message": f"Rapport financier pour la période '{period if period else 'toutes les périodes'}' généré.",
            "report_details": {
                "type": "financier",
                "period": period,
                "data": {"revenus": 5000000, "dépenses": 3000000},
                "url": f"https://example.com/reports/finance_{period if period else 'all'}.pdf"
            }
        }
    else:
        return {"status": "error", "message": f"Type de rapport '{report_type}' non pris en charge."}

def crm_query(query_type: str, client_name: str = None, ticket_id: str = None) -> dict:
    """
    Exécute une requête sur le système CRM (base de données) pour obtenir des informations.
    """
    if query_type == "client_details" and client_name:
        client_obj = Client.query.filter_by(name=client_name).first()
        if client_obj:
            return {"status": "success", "query_type": query_type, "client_name": client_name, "details": client_obj.to_dict()}
        else:
            return {"status": "error", "message": f"Détails du client '{client_name}' non trouvés."}
    
    elif query_type == "ticket_details" and ticket_id:
        ticket_obj = Ticket.query.filter_by(ticket_id_str=ticket_id).first()
        if ticket_obj:
            return {"status": "success", "query_type": query_type, "ticket_id": ticket_id, "details": ticket_obj.to_dict()}
        else:
            return {"status": "error", "message": f"Détails du ticket '{ticket_id}' non trouvés."}
            
    else:
        return {"status": "error", "message": "Type de requête CRM invalide ou paramètres manquants."}

def get_system_status(system_name: str = None) -> dict:
    """
    Récupère le statut d'un système intégré spécifique (CRM System, ERP System, HR Platform) ou de tous les systèmes.
    """
    if system_name:
        status_obj = SystemStatus.query.filter_by(system_name=system_name).first()
        if status_obj:
            return {"status": "success", "system": status_obj.to_dict()}
        else:
            return {"status": "error", "message": f"Statut du système '{system_name}' non trouvé."}
    
    all_statuses = SystemStatus.query.all()
    return {"status": "success", "systems": [s.to_dict() for s in all_statuses]}

def send_email(recipient: str, subject: str, body: str) -> dict:
    """
    Envoie un e-mail à un destinataire spécifié avec un sujet et un corps de texte.
    """
    try:
        new_email = Email(recipient=recipient, subject=subject, body=body, status='Sent')
        db.session.add(new_email)
        db.session.commit()
        return {
            "status": "success",
            "message": f"E-mail envoyé à '{recipient}' avec le sujet '{subject}'.",
            "details": new_email.to_dict()
        }
    except Exception as e:
        db.session.rollback()
        return {"status": "error", "message": f"Erreur lors de l'envoi de l'e-mail: {e}"}

def archive_document(document_id: str, document_type: str, tags: list = None) -> dict:
    """
    Archive un document avec un ID donné, un type et des tags optionnels.
    """
    try:
        new_document = ArchivedDocument(
            document_id_str=document_id,
            document_type=document_type,
            tags=json.dumps(tags) if tags else "[]",
            location="Cloud Storage" # Simulé
        )
        db.session.add(new_document)
        db.session.commit()
        return {
            "status": "success",
            "message": f"Document '{document_id}' de type '{document_type}' archivé avec succès.",
            "details": new_document.to_dict()
        }
    except Exception as e:
        db.session.rollback()
        return {"status": "error", "message": f"Erreur lors de l'archivage du document: {e}"}

def get_archived_documents() -> dict:
    """
    Récupère la liste de tous les documents archivés.
    """
    all_documents = ArchivedDocument.query.all()
    if all_documents:
        return {"status": "success", "documents": [doc.to_dict() for doc in all_documents]}
    else:
        return {"status": "success", "documents": [], "message": "Aucun document archivé trouvé."}

def get_employee_info(employee_name: str) -> dict:
    """
    Récupère des informations détaillées sur un employé spécifique.
    """
    employee = Employee.query.filter_by(name=employee_name).first()
    if employee:
        return {"status": "success", "employee": employee.to_dict()}
    else:
        return {"status": "error", "message": f"Employé '{employee_name}' non trouvé."}

def create_project_task(project_name: str, task_description: str, due_date: str = None, assigned_to: str = None) -> dict:
    """
    Crée une nouvelle tâche pour un projet spécifié.
    """
    try:
        new_task = ProjectTask(
            project_name=project_name,
            task_description=task_description,
            due_date=due_date,
            assigned_to=assigned_to,
            status='Open'
        )
        db.session.add(new_task)
        db.session.commit()
        return {
            "status": "success",
            "message": f"Tâche '{task_description}' créée pour le projet '{project_name}'.",
            "details": new_task.to_dict()
        }
    except Exception as e:
        db.session.rollback()
        return {"status": "error", "message": f"Erreur lors de la création de la tâche de projet: {e}"}


# Mappage des noms de fonctions OpenAI aux fonctions Python réelles
available_functions = {
    "get_sales_data": get_sales_data,
    "schedule_meeting": schedule_meeting,
    "generate_report": generate_report,
    "crm_query": crm_query,
    "get_system_status": get_system_status,
    "send_email": send_email,
    "archive_document": archive_document,
    "get_archived_documents": get_archived_documents,
    "get_employee_info": get_employee_info,
    "create_project_task": create_project_task,
}

# --- Définition des outils (schémas) pour l'API OpenAI ---
tools = [
    {
        "type": "function",
        "function": {
            "name": "get_sales_data",
            "description": "Récupère les chiffres de vente pour un trimestre spécifique (T1, T2, T3, T4) ou toutes les données de vente si aucun trimestre n'est spécifié. Utile pour les requêtes sur les performances commerciales, les comparaisons trimestrielles, ou les détails sur les produits phares.",
            "parameters": {
                "type": "object",
                "properties": {
                    "quarter": {
                        "type": "string",
                        "enum": ["T1", "T2", "T3", "T4", "Q1", "Q2", "Q3", "Q4"],
                        "description": "Le trimestre pour lequel récupérer les données de vente (ex: T1, T2, T3, T4)."
                    }
                },
                "required": []
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "schedule_meeting",
            "description": "Planifie une nouvelle réunion dans le calendrier. Nécessite un sujet, une date et une heure. Les participants sont optionnels.",
            "parameters": {
                "type": "object",
                "properties": {
                    "subject": {
                        "type": "string",
                        "description": "Le sujet ou le titre de la réunion."
                    },
                    "date": {
                        "type": "string",
                        "description": "La date de la réunion au format AAAA-MM-JJ (ex: 2025-07-15)."
                    },
                    "time": {
                        "type": "string",
                        "description": "L'heure de la réunion au format HH:MM (ex: 14:30)."
                    },
                    "participants": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        },
                        "description": "Une liste de noms ou d'adresses e-mail des participants à inviter (ex: ['john.doe@example.com', 'jane.smith@example.com'])."
                    }
                },
                "required": ["subject", "date", "time"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "generate_report",
            "description": "Génère un rapport spécifique. Les types de rapports peuvent inclure 'ventes', 'financier', 'marketing', 'performance'. Peut spécifier une période pour le rapport.",
            "parameters": {
                "type": "object",
                "properties": {
                    "report_type": {
                        "type": "string",
                        "description": "Le type de rapport à générer (ex: 'ventes', 'financier', 'marketing', 'performance')."
                    },
                    "period": {
                        "type": "string",
                        "description": "La période pour laquelle le rapport doit être généré (ex: 'mensuel', 'trimestriel', 'annuel', '2024-Q1')."
                    }
                },
                "required": ["report_type"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "crm_query",
            "description": "Exécute une requête sur le système CRM pour obtenir des informations sur les clients ou les tickets. Peut récupérer les détails d'un client par son nom ou les détails d'un ticket par son ID.",
            "parameters": {
                "type": "object",
                "properties": {
                    "query_type": {
                        "type": "string",
                        "enum": ["client_details", "ticket_details"],
                        "description": "Le type de requête CRM à effectuer (ex: 'client_details' pour les infos client, 'ticket_details' pour les infos ticket)."
                    },
                    "client_name": {
                        "type": "string",
                        "description": "Le nom complet du client dont on veut les détails."
                    },
                    "ticket_id": {
                        "type": "string",
                        "description": "L'ID unique du ticket dont on veut les détails (ex: 'TICKET-001')."
                    }
                },
                "required": ["query_type"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "get_system_status",
            "description": "Récupère le statut opérationnel d'un système intégré spécifique (comme 'CRM System', 'ERP System', 'HR Platform') ou de tous les systèmes. Utile pour vérifier la disponibilité des services.",
            "parameters": {
                "type": "object",
                "properties": {
                    "system_name": {
                        "type": "string",
                        "enum": ["CRM System", "ERP System", "HR Platform"],
                        "description": "Le nom du système dont on veut vérifier le statut (ex: 'CRM System', 'ERP System', 'HR Platform')."
                    }
                },
                "required": []
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "send_email",
            "description": "Envoie un e-mail à un destinataire spécifié avec un sujet et un corps de texte. Utile pour les communications internes ou externes.",
            "parameters": {
                "type": "object",
                "properties": {
                    "recipient": {
                        "type": "string",
                        "description": "L'adresse e-mail du destinataire."
                    },
                    "subject": {
                        "type": "string",
                        "description": "Le sujet de l'e-mail."
                    },
                    "body": {
                        "type": "string",
                        "description": "Le corps de l'e-mail."
                    }
                },
                "required": ["recipient", "subject", "body"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "archive_document",
            "description": "Archive un document en lui assignant un ID, un type et des tags. Utile pour la gestion des documents et l'archivage.",
            "parameters": {
                "type": "object",
                "properties": {
                    "document_id": {
                        "type": "string",
                        "description": "L'ID unique du document à archiver (ex: 'DOC-2025-001')."
                    },
                    "document_type": {
                        "type": "string",
                        "description": "Le type de document (ex: 'contrat', 'facture', 'rapport', 'présentation')."
                    },
                    "tags": {
                        "type": "array",
                        "items": { "type": "string" },
                        "description": "Une liste de tags pour catégoriser le document (ex: ['finance', 'Q2', 'client_X'])."
                    }
                },
                "required": ["document_id", "document_type"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "get_archived_documents",
            "description": "Récupère la liste de tous les documents archivés. Utile pour consulter l'historique des documents archivés.",
            "parameters": {
                "type": "object",
                "properties": {},
                "required": []
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "get_employee_info",
            "description": "Récupère des informations détaillées sur un employé spécifique (poste, département, contact). Utile pour les requêtes RH.",
            "parameters": {
                "type": "object",
                "properties": {
                    "employee_name": {
                        "type": "string",
                        "description": "Le nom complet de l'employé (ex: 'Charles Dubois')."
                    }
                },
                "required": ["employee_name"]
            }
        }
    },
    {
        "type": "function",
        "function": {
            "name": "create_project_task",
            "description": "Crée une nouvelle tâche pour un projet spécifié. Peut inclure une date d'échéance et un responsable.",
            "parameters": {
                "type": "object",
                "properties": {
                    "project_name": {
                        "type": "string",
                        "description": "Le nom du projet auquel la tâche appartient (ex: 'Projet Alpha')."
                    },
                    "task_description": {
                        "type": "string",
                        "description": "La description détaillée de la tâche à créer."
                    },
                    "due_date": {
                        "type": "string",
                        "description": "La date d'échéance de la tâche au format AAAA-MM-JJ (ex: 2025-08-01)."
                    },
                    "assigned_to": {
                        "type": "string",
                        "description": "Le nom de la personne à qui la tâche est assignée."
                    }
                },
                "required": ["project_name", "task_description"]
            }
        }
    }
]


@app.route('/api/chat', methods=['POST'])
def chat():
    if not OPENAI_API_KEY:
        print("Erreur: OPENAI_API_KEY non configurée.")
        return jsonify({"reply": "Désolé, l'IA n'est pas configurée correctement. Veuillez contacter l'administrateur."}), 500

    try:
        data = request.get_json()
        messages = data.get('messages')

        if not messages:
            print("Erreur: Requête invalide, 'messages' manquant.")
            return jsonify({"error": "Requête invalide : le tableau 'messages' est manquant ou invalide."}), 400

        response = client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=messages,
            tools=tools,
            tool_choice="auto"
        )

        message_from_ai = response.choices[0].message
        tool_calls = message_from_ai.tool_calls

        if tool_calls:
            messages.append(message_from_ai)

            for tool_call in tool_calls:
                function_name = tool_call.function.name
                function_args = tool_call.function.arguments
                tool_call_id = tool_call.id

                if function_name in available_functions:
                    function_to_call = available_functions[function_name]
                    
                    try:
                        parsed_args = json.loads(function_args)
                    except json.JSONDecodeError as e:
                        print(f"Erreur de décodage JSON pour les arguments de fonction {function_name}: {e}")
                        messages.append(
                            {
                                "tool_call_id": tool_call_id,
                                "role": "tool",
                                "name": function_name,
                                "content": json.dumps({"error": f"Arguments de fonction invalides pour {function_name}: {e}"})
                            }
                        )
                        continue # Passe à la prochaine tool_call si celle-ci échoue

                    try:
                        function_result = function_to_call(**parsed_args)
                    except TypeError as e:
                        # Gérer spécifiquement les erreurs d'arguments manquants pour les fonctions
                        print(f"Erreur d'arguments manquants pour la fonction {function_name}: {e}")
                        messages.append(
                            {
                                "tool_call_id": tool_call_id,
                                "role": "tool",
                                "name": function_name,
                                "content": json.dumps({"error": f"Arguments manquants ou invalides pour {function_name}: {e}"})
                            }
                        )
                        continue # Passe à la prochaine tool_call si celle-ci échoue
                    except Exception as e:
                        print(f"Erreur lors de l'exécution de la fonction {function_name}: {e}")
                        messages.append(
                            {
                                "tool_call_id": tool_call_id,
                                "role": "tool",
                                "name": function_name,
                                "content": json.dumps({"error": f"Erreur lors de l'exécution de {function_name}: {e}"})
                            }
                        )
                        continue # Passe à la prochaine tool_call si celle-ci échoue
                    
                    formatted_result_content = ""
                    # ... (Logique de formatage des résultats, inchangée) ...
                    if function_name == "get_sales_data":
                        sales_output = []
                        for q, data in function_result.items():
                            if isinstance(data, dict) and 'amount' in data and 'product' in data:
                                sales_output.append(f"**{q}**: ${data['amount']:,.0f} (Produit phare: {data['product']})")
                            elif isinstance(data, dict) and 'error' in data:
                                sales_output.append(f"Erreur: {data['error']}")
                            else:
                                sales_output.append(f"**{q}**: ${data['amount']:,.0f} (Produit phare: {data['product']})")

                        formatted_result_content = "<br>".join(sales_output)
                        if not formatted_result_content and "error" in function_result:
                            formatted_result_content = f"Erreur: {function_result['error']}"
                        elif not formatted_result_content:
                             formatted_result_content = "Aucune donnée de vente trouvée."

                    elif function_name == "schedule_meeting":
                        formatted_result_content = function_result.get("message", "Statut de planification inconnu.")
                        if function_result.get("status") == "success" and function_result.get("details"):
                            details = function_result["details"]
                            formatted_result_content += f"<br>Sujet: {details.get('subject')}<br>Date: {details.get('date')} à {details.get('time')}"
                            if details.get('participants'):
                                formatted_result_content += f"<br>Participants: {', '.join(details['participants'])}"
                    
                    elif function_name == "generate_report":
                        formatted_result_content = function_result.get("message", "Statut de génération de rapport inconnu.")
                        if function_result.get("status") == "success" and function_result.get("report_details"):
                            report_details = function_result["report_details"]
                            formatted_result_content += f"<br>Type: {report_details.get('type')}"
                            if report_details.get('period'):
                                formatted_result_content += f"<br>Période: {report_details.get('period')}"
                            if report_details.get('url'):
                                formatted_result_content += f"<br>URL: <a href='{report_details['url']}' target='_blank' class='text-blue-500 hover:underline'>Accéder au rapport</a>"
                    
                    elif function_name == "crm_query":
                        formatted_result_content = function_result.get("message", "Statut de requête CRM inconnu.")
                        if function_result.get("status") == "success" and function_result.get("details"):
                            details = function_result["details"]
                            if function_result.get("query_type") == "client_details":
                                formatted_result_content = f"Détails pour {function_result.get('client_name')}:<br>"
                                for key, value in details.items():
                                    if key not in ['id']:
                                        formatted_result_content += f" - {key.replace('_', ' ').capitalize()}: {value}<br>"
                            elif function_result.get("query_type") == "ticket_details":
                                formatted_result_content = f"Détails du ticket {function_result.get('ticket_id')}:<br>"
                                for key, value in details.items():
                                    if key not in ['id', 'ticket_id_str']:
                                        formatted_result_content += f" - {key.replace('_', ' ').capitalize()}: {value}<br>"
                        elif function_result.get("status") == "error":
                            formatted_result_content = function_result.get("message", "Erreur lors de la requête CRM.")
                    
                    elif function_name == "get_system_status":
                        formatted_result_content = function_result.get("message", "Statut système inconnu.")
                        if function_result.get("status") == "success" and function_result.get("systems"):
                            formatted_result_content = "Statuts des systèmes intégrés :<br>"
                            for system in function_result["systems"]:
                                status_icon = "🟢" if system['status'] == 'online' else ("🟠" if system['status'] == 'degraded' else "🔴")
                                formatted_result_content += f"{status_icon} **{system['system_name']}**: {system['status'].capitalize()} (Dernière vérification: {datetime.fromisoformat(system['last_checked']).strftime('%Y-%m-%d %H:%M')})<br>"
                        elif function_result.get("status") == "success" and function_result.get("system"):
                            system = function_result["system"]
                            status_icon = "🟢" if system['status'] == 'online' else ("🟠" if system['status'] == 'degraded' else "🔴")
                            formatted_result_content = f"Statut de **{system['system_name']}** : {status_icon} {system['status'].capitalize()} (Dernière vérification: {datetime.fromisoformat(system['last_checked']).strftime('%Y-%m-%d %H:%M')})"
                        elif function_result.get("status") == "error":
                            formatted_result_content = function_result.get("message", "Erreur lors de la récupération du statut du système.")
                    
                    elif function_name == "send_email":
                        formatted_result_content = function_result.get("message", "Statut d'envoi d'e-mail inconnu.")
                        if function_result.get("status") == "success" and function_result.get("details"):
                            details = function_result["details"]
                            formatted_result_content += f"<br>Destinataire: {details.get('recipient')}<br>Sujet: {details.get('subject')}<br>Statut: {details.get('status')}"
                    
                    elif function_name == "archive_document":
                        formatted_result_content = function_result.get("message", "Statut d'archivage inconnu.")
                        if function_result.get("status") == "success" and function_result.get("details"):
                            details = function_result["details"]
                            formatted_result_content += f"<br>ID Document: {details.get('document_id_str')}<br>Type: {details.get('document_type')}"
                            if details.get('tags'):
                                formatted_result_content += f"<br>Tags: {', '.join(details['tags'])}"
                            formatted_result_content += f"<br>Emplacement: {details.get('location')}"

                    elif function_name == "get_archived_documents":
                        if function_result.get("status") == "success" and function_result.get("documents"):
                            documents = function_result["documents"]
                            if documents:
                                formatted_result_content = "Documents archivés :<br>"
                                for doc in documents:
                                    tags_str = f" (Tags: {', '.join(doc['tags'])})" if doc['tags'] else ""
                                    formatted_result_content += f"- **{doc['document_id_str']}** ({doc['document_type']}){tags_str} archivé le {datetime.fromisoformat(doc['archived_at']).strftime('%Y-%m-%d %H:%M')}<br>"
                            else:
                                formatted_result_content = "Aucun document archivé trouvé."
                        elif function_result.get("status") == "error":
                            formatted_result_content = function_result.get("message", "Erreur lors de la récupération des documents archivés.")

                    elif function_name == "get_employee_info":
                        formatted_result_content = function_result.get("message", "Statut de récupération d'infos employé inconnu.")
                        if function_result.get("status") == "success" and function_result.get("employee"):
                            employee = function_result["employee"]
                            formatted_result_content = f"Détails de l'employé **{employee.get('name')}**:<br>"
                            for key, value in employee.items():
                                if key not in ['id']:
                                    formatted_result_content += f" - {key.replace('_', ' ').capitalize()}: {value}<br>"
                        elif function_result.get("status") == "error":
                            formatted_result_content = function_result.get("message", "Erreur lors de la récupération des informations de l'employé.")
                    
                    elif function_name == "create_project_task":
                        formatted_result_content = function_result.get("message", "Statut de création de tâche inconnu.")
                        if function_result.get("status") == "success" and function_result.get("details"):
                            details = function_result["details"]
                            formatted_result_content += f"<br>Projet: {details.get('project_name')}<br>Description: {details.get('task_description')}"
                            if details.get('assigned_to'):
                                formatted_result_content += f"<br>Assigné à: {details.get('assigned_to')}"
                            if details.get('due_date'):
                                formatted_result_content += f"<br>Date d'échéance: {details.get('due_date')}"
                            formatted_result_content += f"<br>Statut: {details.get('status')}"


                    if not formatted_result_content:
                        formatted_result_content = json.dumps(function_result)

                    messages.append(
                        {
                            "tool_call_id": tool_call_id,
                            "role": "tool",
                            "name": function_name,
                            "content": formatted_result_content
                        }
                    )
                else:
                    messages.append(
                        {
                            "tool_call_id": tool_call_id,
                            "role": "tool",
                            "name": function_name,
                            "content": json.dumps({"error": f"Function {function_name} not found."})
                        }
                    )
            
            second_response = client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=messages,
            )
            bot_reply = second_response.choices[0].message.content
            return jsonify({"reply": bot_reply})

        else:
            bot_reply = message_from_ai.content
            return jsonify({"reply": bot_reply})

    except APIConnectionError as e:
        print(f"Erreur de connexion à l'API OpenAI: {e}")
        return jsonify({"reply": f"Désolé, une erreur de connexion est survenue avec l'IA. Veuillez vérifier votre connexion Internet. (Erreur: {e})"}), 500
    except APIStatusError as e:
        print(f"Erreur de statut de l'API OpenAI (code: {e.status_code}): {e.response}")
        if e.status_code == 401:
            return jsonify({"reply": "Désolé, votre clé API OpenAI est invalide ou non autorisée. Veuillez la vérifier."}), 401
        elif e.status_code == 429:
            return jsonify({"reply": "Désolé, les limites de débit de l'API OpenAI ont été atteintes. Veuillez réessayer plus tard."}), 429
        else:
            return jsonify({"reply": f"Désolé, une erreur de l'API OpenAI est survenue (code: {e.status_code}). (Erreur: {e.response})"}), 500
    except Exception as e:
        print(f"Erreur inattendue dans la fonction chat: {e}", exc_info=True) # Ajout de exc_info pour plus de détails
        return jsonify({"reply": f"Désolé, une erreur technique inattendue est survenue. (Erreur: {e})"}), 500
    
    # Fallback pour s'assurer qu'une réponse est toujours renvoyée
    print("Avertissement: La fonction chat a atteint la fin sans retourner explicitement. Ceci ne devrait pas arriver.")
    return jsonify({"reply": "Désolé, une erreur interne inattendue s'est produite."}), 500


# --- Route pour récupérer les statuts des systèmes ---
@app.route('/api/system_statuses', methods=['GET'])
def get_system_statuses_api():
    try:
        all_statuses = SystemStatus.query.all()
        return jsonify({"status": "success", "systems": [s.to_dict() for s in all_statuses]})
    except Exception as e:
        print(f"Erreur lors de la récupération des statuts des systèmes via API: {e}")
        return jsonify({"status": "error", "message": "Erreur lors de la récupération des statuts des systèmes."}), 500

@app.route('/api/users', methods=['GET', 'POST'])
@app.route('/api/users/<int:user_id>', methods=['PUT', 'DELETE'])
def manage_users(user_id=None):
    try:
        if request.method == 'GET':
            users = Employee.query.all()
            return jsonify({
                "status": "success",
                "data": [user.to_dict() for user in users]
            })

        elif request.method == 'POST' and user_id is None:
            data = request.get_json()
            name = data.get('name')
            email = data.get('email')
            if not name or not email:
                return jsonify({"status": "error", "message": "Nom et email requis"}), 400
            if Employee.query.filter_by(email=email).first():
                return jsonify({"status": "error", "message": "Email déjà utilisé"}), 400
            new_user = Employee(name=name, email=email)
            db.session.add(new_user)
            db.session.commit()
            return jsonify({"status": "success", "data": new_user.to_dict()})

        elif request.method == 'PUT' and user_id:
            user = Employee.query.get_or_404(user_id)
            data = request.get_json()
            name = data.get('name')
            email = data.get('email')
            if not name or not email:
                return jsonify({"status": "error", "message": "Nom et email requis"}), 400
            if email != user.email and Employee.query.filter_by(email=email).first():
                return jsonify({"status": "error", "message": "Email déjà utilisé"}), 400
            user.name = name
            user.email = email
            db.session.commit()
            return jsonify({"status": "success", "data": user.to_dict()})

        elif request.method == 'DELETE' and user_id:
            user = Employee.query.get_or_404(user_id)
            db.session.delete(user)
            db.session.commit()
            return jsonify({"status": "success", "message": "Utilisateur supprimé"})

        else:
            return jsonify({"status": "error", "message": "Méthode non autorisée"}), 405

    except Exception as e:
        print(f"Erreur dans /api/users: {str(e)}")
        db.session.rollback()
        return jsonify({"status": "error", "message": f"Erreur lors de la gestion des utilisateurs: {str(e)}"}), 500

@app.route('/api/tickets', methods=['GET'])
def get_tickets_api():
    try:
        tickets = Ticket.query.all()
        status_counts = {"open": 0, "in_progress": 0, "closed": 0}
        for ticket in tickets:
            status = ticket.status.lower()
            if status in status_counts:
                status_counts[status] += 1
        return jsonify({"status": "success", "data": status_counts})
    except Exception as e:
        print(f"Erreur dans /api/tickets: {str(e)}")
        return jsonify({"status": "error", "message": f"Erreur lors de la récupération des tickets: {str(e)}"}), 500

@app.route('/api/sales', methods=['GET'])
def get_sales_api():
    try:
        sales = Sales.query.all()  # Assumes a Sales model
        sales_data = [{"id": s.id, "amount": s.amount, "date": s.date.isoformat()} for s in sales]
        return jsonify({"status": "success", "data": sales_data})
    except Exception as e:
        print(f"Erreur dans /api/sales: {str(e)}")
        return jsonify({"status": "error", "message": f"Erreur lors de la récupération des ventes: {str(e)}"}), 500

if __name__ == '__main__':
    # Initialisation des données de la base de données
    with app.app_context():
        db.create_all() # Crée les tables si elles n'existent pas

        # Charger les données initiales si la base est vide
        if Sale.query.count() == 0:
            print("Chargement des données de vente initiales...")
            sales = [
                Sale(quarter='T1', amount=1250000, product='Nexus Alpha'),
                Sale(quarter='T2', amount=1780000, product='Nexus X200'),
                Sale(quarter='T3', amount=1500000, product='Nexus Beta'),
                Sale(quarter='T4', amount=2100000, product='Nexus Prime')
            ]
            db.session.add_all(sales)
            db.session.commit()
            print("Données de vente chargées.")

        if Client.query.count() == 0:
            print("Chargement des données client initiales...")
            clients = [
                Client(name="Alice Dupont", email="alice.dupont@example.com", company="Tech Solutions Inc.", status="Active"),
                Client(name="Bob Martin", email="bob.martin@example.com", company="Global Innovations", status="Lead")
            ]
            db.session.add_all(clients)
            db.session.commit()
            print("Données client chargées.")

        if Ticket.query.count() == 0:
            print("Chargement des données de ticket initiales...")
            tickets = [
                Ticket(ticket_id_str="TICKET-001", subject="Problème de connexion", status="Ouvert", assigned_to="Support Team"),
                Ticket(ticket_id_str="TICKET-002", subject="Demande de fonctionnalité", status="Fermé", assigned_to="Product Team")
            ]
            db.session.add_all(tickets)
            db.session.commit()
            print("Données de ticket chargées.")

        if SystemStatus.query.count() == 0:
            print("Chargement des statuts des systèmes initiaux...")
            statuses = [
                SystemStatus(system_name="CRM System", status="online"),
                SystemStatus(system_name="ERP System", status="online"),
                SystemStatus(system_name="HR Platform", status="offline")
            ]
            db.session.add_all(statuses)
            db.session.commit()
            print("Statuts des systèmes chargés.")
        
        if Employee.query.count() == 0:
            print("Chargement des données d'employés initiales...")
            employees = [
                Employee(name="Charles Dubois", position="Développeur Senior", department="R&D", email="charles.dubois@example.com", phone="555-1234", hire_date="2020-01-15"),
                Employee(name="Émilie Bernard", position="Chef de Projet", department="Opérations", email="emilie.bernard@example.com", phone="555-5678", hire_date="2018-06-01")
            ]
            db.session.add_all(employees)
            db.session.commit()
            print("Données d'employés chargées.")

        if ProjectTask.query.count() == 0:
            print("Chargement des données de tâches de projet initiales...")
            tasks = [
                ProjectTask(project_name="Projet Alpha", task_description="Développement de la fonctionnalité X", due_date="2025-08-01", assigned_to="Charles Dubois", status="In Progress"),
                ProjectTask(project_name="Projet Beta", task_description="Rédaction du rapport financier", due_date="2025-07-20", assigned_to="Émilie Bernard", status="Open")
            ]
            db.session.add_all(tasks)
            db.session.commit()
            print("Données de tâches de projet chargées.")

    app.run(port=5000, debug=True)
