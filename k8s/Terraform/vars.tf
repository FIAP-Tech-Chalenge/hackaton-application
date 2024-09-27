variable "regionDefault" {
  default = "us-east-1"
}

variable "labRole" {
  default = "arn:aws:iam::XXXXXXXXXXXXX:role/LabRole"
}

variable "projectName" {
  default = "tech"
}

variable "subnetA" {
  default = "subnet-0354b4e866aa2e0e4"
}

variable "subnetB" {
  default = "subnet-0973e7bb30f9de47c"
}

variable "subnetC" {
  default = "subnet-0dba9cc714234362b"
}

variable "vpcId" {
  default = "vpc-06fac7a1fe56de12b"
}

variable "instanceType" {
  default = "t3a.medium"
}

variable "principalArn" {
  default = "arn:aws:iam::XXXXXXXXXXXXX:role/voclabs"
}

variable "policyArn" {
  default = "arn:aws:eks::aws:cluster-access-policy/AmazonEKSClusterAdminPolicy"
}

variable "accessConfig" {
  default = "API_AND_CONFIG_MAP"
}

variable "aws_access_key_id" {
  description = "AWS access key ID"
  type        = string
}

variable "aws_secret_access_key" {
  description = "AWS secret access key"
  type        = string
}

variable "aws_session_token" {
  description = "AWS Session Token"
  type        = string
}


